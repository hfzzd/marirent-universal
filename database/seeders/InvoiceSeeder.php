<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'user@mariarental.com')->first();

        if (!$customer) {
            return;
        }

        $rentals = Rental::where('user_id', $customer->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        if ($rentals->isEmpty()) {
            return;
        }

        foreach ($rentals as $index => $rental) {
            $invoiceNumber = 'INV' . str_pad($index + 1, 5, '0', STR_PAD_LEFT);
            $subtotal = $rental->subtotal;
            $discount = $rental->discount;
            $tax = $rental->tax;
            $total = $rental->total_amount;

            $isPaid = $rental->status === 'completed';

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'rental_id' => $rental->id,
                'user_id' => $customer->id,
                'type' => 'rental',
                'status' => $isPaid ? 'paid' : 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid_amount' => $isPaid ? $total : 0,
                'payment_method' => $isPaid ? 'Transfer Bank' : null,
                'payment_date' => $isPaid ? now() : null,
                'due_date' => $rental->start_date->copy()->addDays(7)->toDateTimeString(),
                'notes' => 'Invoice otomatis untuk sewa ' . $rental->rental_code,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Sewa ' . $rental->vehicle->name . ' (' . $rental->total_days . ' hari)',
                'quantity' => $rental->total_days,
                'unit_price' => $rental->daily_rate,
                'total' => $subtotal,
            ]);

            if ($rental->driver_fee > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Biaya supir (' . $rental->total_days . ' hari)',
                    'quantity' => $rental->total_days,
                    'unit_price' => $rental->driver_fee / $rental->total_days,
                    'total' => $rental->driver_fee,
                ]);
            }
        }
    }
}
