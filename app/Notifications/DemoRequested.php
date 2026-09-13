<?php

namespace App\Notifications;

use App\Models\DemoRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class DemoRequested extends Notification
{
    use Queueable;

    public function __construct(public DemoRequest $demo) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $jadwal = $this->demo->preferred_date?->format('d M Y') . ' ' . $this->demo->preferred_time;

        return new DatabaseMessage([
            'title' => 'Permintaan Jadwal Demo Baru',
            'message' => "{$this->demo->name} ({$this->demo->business_name}) meminta demo pada {$jadwal}. WA: {$this->demo->phone}.",
            'type' => 'demo_requested',
            'url' => null,
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}
