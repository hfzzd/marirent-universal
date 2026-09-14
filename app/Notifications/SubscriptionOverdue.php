<?php

namespace App\Notifications;

use App\Models\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class SubscriptionOverdue extends Notification
{
    use Queueable;

    public function __construct(public Merchant $merchant) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'Akun Diblokir — Subscription Menunggak',
            'message' => sprintf(
                'Tagihan subscription %s belum dibayar dan telah melewati jatuh tempo. Akun toko tidak dapat digunakan sampai pembayaran lunas.',
                $this->merchant->name ?? 'toko Anda'
            ),
            'url' => url('/subscriptions/due'),
            'type' => 'subscription_overdue',
            'merchant_id' => $this->merchant->id,
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}