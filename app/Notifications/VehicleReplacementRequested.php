<?php

namespace App\Notifications;

use App\Models\VehicleReplacement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class VehicleReplacementRequested extends Notification
{
    use Queueable;

    public function __construct(public VehicleReplacement $replacement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $booking = $this->replacement->booking;

        return new DatabaseMessage([
            'title' => 'Permintaan Penggantian Kendaraan',
            'message' => sprintf(
                'Permintaan penggantian %s → %s diajukan oleh %s untuk booking %s. Alasan: %s',
                $this->replacement->originalVehicle?->name ?? '-',
                $this->replacement->replacementVehicle?->name ?? '-',
                $this->replacement->requestedBy?->name ?? '-',
                $booking?->booking_code ?? '-',
                $this->replacement->reason ?? '-'
            ),
            'reference_code' => $booking?->booking_code,
            'booking_id' => $booking?->id,
            'url' => $booking ? url("/replacements/{$this->replacement->id}") : null,
            'type' => 'vehicle_replacement_requested',
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}