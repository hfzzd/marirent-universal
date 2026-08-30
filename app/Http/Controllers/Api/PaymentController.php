<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'user']);

        if ($request->invoice_id) {
            $query->where('invoice_id', $request->invoice_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(min($request->get('per_page', 15), 50));

        return response()->json(['success' => true, 'data' => $payments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,transfer,ewallet,credit_card,other',
            'reference_number' => 'nullable|string|max:100',
            'proof_photo' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        if ($validated['amount'] > $invoice->getRemainingAmount()) {
            return response()->json([
                'success' => false, 'message' => 'Jumlah pembayaran melebihi tagihan',
            ], 422);
        }

        $payment = Payment::create([
            'payment_code' => Payment::generatePaymentCode(),
            'invoice_id' => $invoice->id,
            'user_id' => $request->user()->id,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_photo' => $validated['proof_photo'] ?? null,
            'status' => $validated['method'] === 'cash' ? 'verified' : 'pending',
            'notes' => $validated['notes'] ?? null,
            'paid_at' => now(),
        ]);

        if ($payment->status === 'verified') {
            $invoice = $payment->invoice->refresh();
            $this->finalizeInvoice($invoice, $payment);
        }

        return response()->json([
            'success' => true, 'message' => 'Pembayaran berhasil', 'data' => $payment,
        ], 201);
    }

    public function verify(Payment $payment, Request $request)
    {
        if ($payment->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Pembayaran sudah diproses'], 422);
        }

        $payment->update([
            'status' => 'verified',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $invoice = $payment->invoice->refresh();
        $this->finalizeInvoice($invoice, $payment);

        return response()->json([
            'success' => true, 'message' => 'Pembayaran diverifikasi', 'data' => $payment,
        ]);
    }

    public function reject(Payment $payment, Request $request)
    {
        $payment->update([
            'status' => 'rejected',
            'notes' => $request->get('notes', 'Pembayaran ditolak'),
        ]);

        return response()->json([
            'success' => true, 'message' => 'Pembayaran ditolak', 'data' => $payment,
        ]);
    }

    private function finalizeInvoice(Invoice $invoice, Payment $payment): void
    {
        $invoice->update([
            'payment_method' => $payment->method,
            'payment_reference' => $payment->reference_number,
            'paid_at' => $invoice->paid_amount >= $invoice->total_amount ? now() : $invoice->paid_at,
        ]);

        $bookings = collect();
        if ($invoice->booking) {
            $bookings->push($invoice->booking);
        }
        $invoice->bookings()->each(fn($b) => $bookings->push($b));

        $statusMap = ['paid' => 'paid', 'partial' => 'partial'];
        $bookingStatus = $statusMap[$invoice->status] ?? 'unpaid';
        $bookings->unique('id')->each(fn($b) => $b->update(['payment_status' => $bookingStatus]));
    }
}
