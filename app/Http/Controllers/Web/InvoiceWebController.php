<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['booking', 'user', 'items']);

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
        $invoice->load(['booking', 'user', 'owner', 'items', 'payments']);
        return view('invoices.show', compact('invoice'));
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
