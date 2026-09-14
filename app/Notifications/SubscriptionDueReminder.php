<?php

namespace App\Notifications;

use App\Models\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class SubscriptionDueReminder extends Notification
{
    use Queueable;

    public function __construct(
        public Merchant $merchant,
        public string $dueDate,
        public string $amount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'Tagihan Subscription Jatuh Tempo',
            'message' => sprintf(
                'Tagihan subscription %s sebesar Rp %s jatuh tempo %s. Segera lakukan pembayaran agar akun tidak diblokir.',
                $this->merchant->name ?? 'toko Anda',
                $this->amount,
                $this->dueDate
            ),
            'url' => url('/subscriptions/history'),
            'type' => 'subscription_due_reminder',
            'merchant_id' => $this->merchant->id,
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}