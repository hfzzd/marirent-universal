<?php

namespace App\Notifications;

use App\Models\MerchantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class SubscriptionPaid extends Notification
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
            'title' => 'Pembayaran Subscription Disetujui',
            'message' => sprintf(
                'Pembayaran subscription %s sebesar Rp %s telah diverifikasi. Akun toko aktif kembali hingga %s.',
                $merchant?->name ?? 'toko Anda',
                number_format((float) $this->subscription->amount, 0, ',', '.'),
                $merchant?->subscription_until?->format('d M Y') ?? '-'
            ),
            'url' => url('/subscriptions/history'),
            'type' => 'subscription_paid',
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}