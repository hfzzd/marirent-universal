<?php

namespace App\Notifications;

use App\Models\MerchantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class SubscriptionPaymentAwaitingVerification extends Notification
{
    use Queueable;

    public function __construct(public MerchantSubscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $merchant = $this->subscription->merchant;

        return new DatabaseMessage([
            'title' => 'Bukti Pembayaran Subscription Menunggu Verifikasi',
            'message' => sprintf(
                'Pembayaran subscription %s sebesar Rp %s (periode %s s.d. %s) menunggu verifikasi.',
                $merchant?->name ?? 'merchant',
                number_format((float) $this->subscription->amount, 0, ',', '.'),
                $this->subscription->period_start?->format('d M Y'),
                $this->subscription->period_end?->format('d M Y')
            ),
            'url' => $merchant ? url("/superadmin/subscriptions/{$merchant->id}") : null,
            'type' => 'subscription_payment_awaiting_verification',
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}