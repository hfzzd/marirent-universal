<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Invoice;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->recalculateInvoice($payment);
    }

    public function updated(Payment $payment): void
    {
        if ($payment->isDirty('status')) {
            $this->recalculateInvoice($payment);
        }
    }

    public function deleted(Payment $payment): void
    {
        $this->recalculateInvoice($payment);
    }

    private function recalculateInvoice(Payment $payment): void
    {
        $invoice = $payment->invoice;
        if (!$invoice) return;

        $verifiedTotal = $invoice->payments()
            ->where('status', 'verified')
            ->sum('amount');

        $rejectedTotal = $invoice->payments()
            ->where('status', 'rejected')
            ->sum('amount');

        $effectivePaid = $verifiedTotal;

        $newStatus = match(true) {
            $effectivePaid >= $invoice->total_amount && $invoice->total_amount > 0 => 'paid',
            $effectivePaid > 0 => 'partial',
            default => 'unpaid',
        };

        $invoice->update([
            'paid_amount' => $effectivePaid,
            'due_amount' => max(0, $invoice->total_amount - $effectivePaid),
            'status' => $newStatus,
        ]);

        if ($invoice->booking) {
            $bookingPaymentStatus = match($newStatus) {
                'paid' => 'paid',
                'partial' => 'partial',
                default => 'unpaid',
            };
            $invoice->booking->update(['payment_status' => $bookingPaymentStatus]);
        }
    }
}
