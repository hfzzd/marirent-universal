<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['booking', 'booking.vehicle', 'booking.vehicle.category', 'booking.category', 'user', 'items']);

        if (Auth::user()->role === 'user') {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->isMerchantStaff()) {
            $query->where('owner_id', Auth::user()->merchantId());
            $categoryId = Auth::user()->merchantCategoryId();
            if ($categoryId) {
                $query->where(function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId)
                      ->orWhereHas('booking', fn($bq) => $bq->where('category_id', $categoryId)->orWhereHas('childBookings', fn($cq) => $cq->where('category_id', $categoryId)))
                      ->orWhereHas('bookings', fn($bq) => $bq->where('category_id', $categoryId)->orWhereHas('childBookings', fn($cq) => $cq->where('category_id', $categoryId)));
                });
            }
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = "%{$request->search}%";
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', $search)
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', $search)->orWhere('email', 'like', $search))
                  ->orWhereHas('booking', fn($bq) => $bq->where('booking_code', 'like', $search));
            });
        }

        if ($request->date_from) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->user_id && Auth::user()->role !== 'user') {
            $query->where('user_id', $request->user_id);
        }

        $invoices = $query->latest()->paginate(15);
        $customers = User::where('role', 'user')->get();

        return view('invoices.index', compact('invoices', 'customers'));
    }

    public function create()
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403);
        }

        $customers = User::where('role', 'user')->get();

        $query = Booking::whereIn('status', ['confirmed', 'ongoing', 'completed'])
            ->where('payment_status', '!=', 'paid')
            ->whereDoesntHave('invoice')
            ->whereDoesntHave('invoices')
            ->with(['vehicle', 'category', 'user']);

        if (Auth::user()->isMerchantStaff()) {
            $query->ownedByMerchant(Auth::user()->merchantId());
        }

        $bookings = $query->get();

        $eligible = $bookings->map(function ($b) {
            return [
                'id' => $b->id,
                'user_id' => $b->user_id,
                'code' => $b->booking_code,
                'unit' => $b->vehicle->name ?? ($b->category->name ?? '-'),
                'period' => ($b->start_date ? $b->start_date->format('d M Y') : '?') . ' - ' . ($b->end_date ? $b->end_date->format('d M Y') : '?'),
                'days' => $b->start_date && $b->end_date ? max(1, $b->start_date->diffInDays($b->end_date)) : 0,
                'price' => (float) $b->final_price,
            ];
        })->filter(fn($e) => $e['price'] > 0)->values();

        return view('invoices.create', compact('customers', 'eligible'));
    }

    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'booking_ids' => 'required|array|min:1',
            'booking_ids.*' => 'exists:bookings,id',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:2000',
        ]);

        $bookings = Booking::whereIn('id', $validated['booking_ids'])->with(['vehicle', 'category'])->get();
        $ownerId = Auth::user()->merchantId() ?? Auth::id();

        $subtotal = $bookings->sum('final_price');
        $taxPercent = $validated['tax_percent'] ?? 0;
        $taxAmount = round($subtotal * $taxPercent / 100, 2);
        $discountAmount = $validated['discount_amount'] ?? 0;
        $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber('rental'),
            'booking_id' => $bookings->first()->id,
            'user_id' => $validated['user_id'],
            'owner_id' => $ownerId,
            'type' => 'rental',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'due_amount' => $totalAmount,
            'status' => 'draft',
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($bookings as $b) {
            $vehicleName = $b->vehicle->name ?? ($b->category->name ?? '-');
            $days = max(1, $b->start_date->diffInDays($b->end_date));
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "Sewa {$vehicleName} ({$b->booking_code}) - {$days} hari",
                'quantity' => 1,
                'unit_price' => (float) $b->final_price,
                'total_price' => (float) $b->final_price,
            ]);
        }

        $invoice->bookings()->attach($bookings->pluck('id'));

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice gabungan berhasil dibuat dengan ' . $bookings->count() . ' booking.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeInvoiceAccess($invoice);
        $invoice->load(['booking', 'booking.vehicle', 'booking.bookingItems', 'booking.category', 'user', 'owner', 'items', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $this->authorizeInvoiceAccess($invoice);
        $invoice->load(['booking', 'booking.vehicle', 'booking.bookingItems', 'booking.category', 'user', 'owner', 'items', 'payments']);
        return view('invoices.print', compact('invoice'));
    }

    public function send(Invoice $invoice)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403);
        }

        if (Auth::user()->isMerchantStaff() && (int) $invoice->owner_id !== Auth::user()->merchantId()) {
            abort(403);
        }

        if (Auth::user()->isMerchantStaff() && !$this->invoiceBelongsToStaffCategory($invoice)) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice sudah lunas dan tidak bisa dikirim ulang.');
        }

        $invoice->update(['status' => 'sent']);

        return back()->with('success', 'Invoice berhasil dikirim ulang.');
    }

    private function invoiceBelongsToStaffCategory(Invoice $invoice): bool
    {
        $user = Auth::user();
        $categoryId = $user->merchantCategoryId();

        if (!$categoryId) {
            return true;
        }

        $booking = $invoice->primaryBooking();

        if (!$booking) {
            return (int) $invoice->category_id === $categoryId;
        }

        return $booking->belongsToCategory($categoryId);
    }

    private function authorizeInvoiceAccess(Invoice $invoice): void
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            abort_unless($invoice->user_id === $user->id, 403);
            return;
        }
        if ($user->role === 'driver') {
            $driver = \App\Models\Driver::where('user_id', $user->id)->first();
            if (!$driver || (int) $invoice->owner_id !== (int) $driver->owner_id) {
                abort(403);
            }
            return;
        }
        if ($user->isMerchantStaff() && $invoice->owner_id && (int) $invoice->owner_id !== $user->merchantId()) {
            abort(403);
        }
        if ($user->isMerchantStaff() && !$this->invoiceBelongsToStaffCategory($invoice)) {
            abort(403);
        }
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,transfer,ewallet,credit_card,other',
            'reference_number' => 'nullable|string|max:100',
            'proof_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['amount'] > $invoice->getRemainingAmount()) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan (Rp ' . number_format($invoice->getRemainingAmount(), 0, ',', '.') . ')');
        }

        $booking = $invoice->primaryBooking();
        if ($booking && $booking->payment_plan === 'dp50') {
            $dp = (float) $booking->getDpAmount();
            if ($dp > 0 && (float) $invoice->paid_amount == 0 && (float) $validated['amount'] < $dp) {
                return back()->with('error', 'Booking ini memakai skema DP 50%. Pembayaran pertama minimal Rp ' . number_format($dp, 0, ',', '.'));
            }
        }

        $proofPath = null;
        if ($request->hasFile('proof_photo')) {
            $proofPath = $request->file('proof_photo')->store('payments/proof', 'public');
        }

        $payment = Payment::create([
            'payment_code' => Payment::generatePaymentCode(),
            'invoice_id' => $invoice->id,
            'user_id' => Auth::id(),
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_photo' => $proofPath,
            'status' => $validated['method'] === 'cash' ? 'verified' : 'pending',
            'notes' => $validated['notes'] ?? null,
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.');
    }

    public function verifyPayment(Payment $payment)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403);
        }

        if (Auth::user()->isMerchantStaff() && $payment->invoice && (int) $payment->invoice->owner_id !== Auth::user()->merchantId()) {
            abort(403);
        }

        if (Auth::user()->isMerchantStaff() && $payment->invoice && !$this->invoiceBelongsToStaffCategory($payment->invoice)) {
            abort(403);
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran sudah diproses');
        }

        $payment->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $invoice = $payment->invoice->refresh();

        $invoice->update([
            'payment_method' => $payment->method,
            'payment_reference' => $payment->reference_number,
        ]);

        $this->refreshInvoiceFromPayments($invoice);

        return back()->with('success', 'Pembayaran berhasil diverifikasi');
    }

    public function rejectPayment(Request $request, Payment $payment)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'owner', 'admin'])) {
            abort(403);
        }

        if (Auth::user()->isMerchantStaff() && $payment->invoice && (int) $payment->invoice->owner_id !== Auth::user()->merchantId()) {
            abort(403);
        }

        if (Auth::user()->isMerchantStaff() && $payment->invoice && !$this->invoiceBelongsToStaffCategory($payment->invoice)) {
            abort(403);
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran sudah diproses');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $payment->update([
            'status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $this->refreshInvoiceFromPayments($payment->invoice->fresh());

        return back()->with('success', 'Pembayaran ditolak');
    }

    private function refreshInvoiceFromPayments(Invoice $invoice): void
    {
        if (!$invoice) {
            return;
        }

        $total = (float) $invoice->total_amount;
        $paid = (float) $invoice->payments()->where('status', 'verified')->sum('amount');
        $due = max(0, $total - $paid);

        $invoice->update([
            'paid_amount' => $paid,
            'due_amount' => $due,
            'paid_at' => $paid >= $total && $total > 0 ? now() : $invoice->paid_at,
            'status' => $paid >= $total && $total > 0 ? 'paid' : ($paid > 0 ? 'partial' : 'sent'),
        ]);

        $this->syncLinkedBookings($invoice->fresh());
    }

    private function syncLinkedBookings(Invoice $invoice): void
    {
        if (!$invoice) return;

        $bookingStatus = match($invoice->status) {
            'paid' => 'paid',
            'partial' => 'partial',
            default => 'unpaid',
        };

        $bookings = collect();
        if ($invoice->booking) {
            $bookings->push($invoice->booking);
        }
        $invoice->bookings()->each(fn($b) => $bookings->push($b));

        $bookings->unique('id')->each(fn($b) => $b->update(['payment_status' => $bookingStatus]));
    }
}
