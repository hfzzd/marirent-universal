<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Booking;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Invoice::with(['booking', 'user', 'items', 'payments']);

        if ($user->role === 'user') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'owner' || $user->role === 'superadmin') {
            $query->where('owner_id', $user->id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(min($request->get('per_page', 15), 50));

        return response()->json(['success' => true, 'data' => $invoices]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'type' => 'required|in:rental,driver_salary,replacement,damage,other',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'due_date' => 'required|date|after:now',
            'notes' => 'nullable|string|max:2000',
            'terms' => 'nullable|string|max:2000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $user = $request->user();

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $taxRate = $validated['tax_rate'] ?? 11;
        $taxAmount = $subtotal * ($taxRate / 100);
        $discount = $validated['discount_amount'] ?? 0;
        $totalAmount = $subtotal + $taxAmount - $discount;

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber($validated['type']),
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'owner_id' => $user->id,
            'type' => $validated['type'],
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discount,
            'total_amount' => $totalAmount,
            'due_amount' => $totalAmount,
            'status' => 'draft',
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'success' => true, 'message' => 'Invoice berhasil dibuat', 'data' => $invoice->load('items'),
        ], 201);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['booking', 'user', 'owner', 'items', 'payments']);
        return response()->json(['success' => true, 'data' => $invoice]);
    }

    public function send(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Invoice sudah terkirim'], 422);
        }

        $invoice->update(['status' => 'sent']);

        return response()->json([
            'success' => true, 'message' => 'Invoice berhasil dikirim', 'data' => $invoice,
        ]);
    }

    public function byBooking(string $bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();
        $invoice = Invoice::with(['items', 'payments'])
            ->where('booking_id', $booking->id)
            ->first();

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $invoice]);
    }
}
