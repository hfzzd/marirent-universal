<?php

namespace App\Notifications;

use App\Models\VehicleReplacement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class VehicleReplaced extends Notification
{
    use Queueable;

    public function __construct(public VehicleReplacement $replacement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $referenceCode = $this->replacement->booking?->booking_code
            ?? $this->replacement->rental?->rental_code
            ?? '-';

        return new DatabaseMessage([
            'title' => 'Kendaraan Sewa Diganti',
            'message' => sprintf(
                'Kendaraan Anda (%s) diganti menjadi %s. Alasan: %s%s',
                $this->replacement->originalVehicle?->name ?? '-',
                $this->replacement->replacementVehicle?->name ?? '-',
                $this->replacement->reason,
                (float) $this->replacement->price_difference > 0
                    ? sprintf(' | Biaya tambahan: Rp %s', number_format((float) $this->replacement->price_difference, 0, ',', '.'))
                    : ''
            ),
            'reference_code' => $referenceCode,
            'original_vehicle' => $this->replacement->originalVehicle?->name,
            'replacement_vehicle' => $this->replacement->replacementVehicle?->name,
            'reason' => $this->replacement->reason,
            'price_difference' => (float) $this->replacement->price_difference,
            'swapped_at' => optional($this->replacement->swapped_at)->toDateTimeString(),
            'type' => 'vehicle_replaced',
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}
