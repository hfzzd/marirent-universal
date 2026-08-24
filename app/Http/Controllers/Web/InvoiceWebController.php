<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['booking', 'bookings', 'user', 'items']);

        if (Auth::user()->role === 'user') {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->role === 'owner') {
            $query->where('owner_id', Auth::id());
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['booking', 'bookings', 'user', 'owner', 'items', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function create()
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin atau owner yang dapat membuat invoice.');
        }

        $customers = User::where('role', 'user')->orderBy('name')->get();

        $eligible = Booking::with(['vehicle', 'category'])
            ->whereIn('status', ['confirmed', 'ongoing'])
            ->whereDoesntHave('invoice')
            ->whereDoesntHave('invoices')
            ->orderBy('user_id')
            ->orderBy('start_date')
            ->get()
            ->map(function (Booking $b) {
                $unit = $b->vehicle?->name
                    ?? ($b->item && !$b->item instanceof Booking ? ($b->item->name ?? null)
                        : $b->category?->name)
                    ?? 'Unit Sewa';
                $days = max(1, (int) ceil($b->start_date->diffInHours($b->end_date) / 24));

                return [
                    'id' => $b->id,
                    'user_id' => $b->user_id,
                    'customer' => $b->user?->name ?? '-',
                    'code' => $b->booking_code,
                    'unit' => $unit,
                    'period' => $b->start_date->translatedFormat('d M Y') . ' – ' . $b->end_date->translatedFormat('d M Y'),
                    'days' => $days,
                    'price' => (float) $b->final_price,
                ];
            });

        return view('invoices.create', compact('customers', 'eligible'));
    }

    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner'])) {
            abort(403, 'Hanya superadmin atau owner yang dapat membuat invoice.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'booking_ids' => 'required|array|min:1',
            'booking_ids.*' => 'integer|exists:bookings,id',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        $bookings = Booking::with(['vehicle', 'category', 'item'])
            ->whereIn('id', $validated['booking_ids'])
            ->where('user_id', $validated['user_id'])
            ->whereIn('status', ['confirmed', 'ongoing'])
            ->whereDoesntHave('invoice')
            ->whereDoesntHave('invoices')
            ->get();

        if ($bookings->isEmpty()) {
            return back()->withInput()->with('error', 'Tidak ada booking valid yang bisa diinvoice.');
        }
        if ($bookings->count() < count($validated['booking_ids'])) {
            return back()->withInput()->with('error', 'Beberapa booking tidak valid / sudah memiliki invoice.');
        }

        $subtotal = (float) $bookings->sum('final_price');
        $taxPercent = (float) ($validated['tax_percent'] ?? 0);
        $taxAmount = round($subtotal * $taxPercent / 100);
        $discount = min((float) ($validated['discount_amount'] ?? 0), $subtotal);
        $total = max(0, $subtotal + $taxAmount - $discount);

        $invoice = DB::transaction(function () use ($validated, $bookings, $subtotal, $taxAmount, $discount, $total) {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber('rental'),
                'booking_id' => $bookings->first()->id,
                'user_id' => $validated['user_id'],
                'owner_id' => Auth::id(),
                'type' => 'rental',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'paid_amount' => 0,
                'due_amount' => $total,
                'status' => 'sent',
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? ($bookings->count() > 1 ? 'Invoice gabungan ' . $bookings->count() . ' sewa.' : null),
            ]);

            $invoice->bookings()->attach($bookings->pluck('id'));

            foreach ($bookings as $b) {
                $unit = $b->vehicle?->name
                    ?? ($b->item && !$b->item instanceof Booking ? ($b->item->name ?? null) : null)
                    ?? $b->category?->name
                    ?? 'Unit Sewa';
                $days = max(1, (int) ceil($b->start_date->diffInHours($b->end_date) / 24));

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => sprintf(
                        'Sewa %s (%s) — %s s/d %s (%d hari)',
                        $unit,
                        $b->booking_code,
                        $b->start_date->translatedFormat('d M Y'),
                        $b->end_date->translatedFormat('d M Y'),
                        $days
                    ),
                    'quantity' => $days,
                    'unit_price' => round((float) $b->final_price / $days),
                    'total_price' => (float) $b->final_price,
                ]);
            }

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' berhasil dibuat untuk ' . $bookings->count() . ' sewa.');
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,transfer,ewallet,credit_card,other',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment = Payment::create([
            'payment_code' => Payment::generatePaymentCode(),
            'invoice_id' => $invoice->id,
            'user_id' => Auth::id(),
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'status' => $validated['method'] === 'cash' ? 'verified' : 'pending',
            'notes' => $validated['notes'] ?? null,
            'paid_at' => now(),
        ]);

        if ($payment->status === 'verified') {
            $newPaid = $invoice->paid_amount + $payment->amount;
            $invoice->update([
                'paid_amount' => $newPaid,
                'due_amount' => max(0, $invoice->total_amount - $newPaid),
                'status' => $newPaid >= $invoice->total_amount ? 'paid' : 'partial',
            ]);
        }

        return back()->with('success', 'Pembayaran berhasil dicatat');
    }
}
