<?php

namespace App\Notifications;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class MaintenanceNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Vehicle $vehicle,
        public string $title,
        public string $type = 'maintenance_baru',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $statusLabel = match($this->type) {
            'maintenance_baru' => 'Jadwal maintenance baru dibuat',
            'maintenance_in_progress' => 'Maintenance sedang dikerjakan',
            'maintenance_completed' => 'Maintenance selesai dikerjakan',
            'maintenance_cancelled' => 'Maintenance dibatalkan',
            default => 'Info maintenance',
        };

        return new DatabaseMessage([
            'title' => $statusLabel,
            'message' => "{$this->title} untuk {$this->vehicle->name}. Silakan cek jadwal maintenance merchant Anda.",
            'type' => 'maintenance',
            'maintenance_type' => $this->type,
            'vehicle_name' => $this->vehicle->name,
            'url' => route('maintenances.index'),
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}