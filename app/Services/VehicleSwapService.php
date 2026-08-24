<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Rental;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleReplacement;
use App\Notifications\VehicleReplaced;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleSwapService
{
    /**
     * Tukar unit kendaraan pada booking yang sedang berjalan (confirmed/ongoing).
     */
    public function swapForBooking(
        Booking $booking,
        Vehicle $replacementVehicle,
        User $actor,
        string $reason = '',
        ?float $priceDifference = null,
        bool $markMaintenance = true
    ): VehicleReplacement {
        return $this->execute(
            subject: 'booking',
            model: $booking,
            currentVehicleId: $booking->vehicle_id,
            isActive: fn () => in_array($booking->status, ['confirmed', 'ongoing']),
            newUnitStatus: $booking->status === 'ongoing' ? 'rented' : 'reserved',
            applySwap: function (Vehicle $newVehicle) use ($booking, $priceDifference) {
                $finalPrice = max(0, (float) $booking->final_price + (float) ($priceDifference ?? 0));

                $booking->update([
                    'vehicle_id' => $newVehicle->id,
                    'final_price' => $finalPrice,
                ]);
            },
            replacementVehicle: $replacementVehicle,
            actor: $actor,
            reason: $reason,
            priceDifference: $priceDifference,
            markMaintenance: $markMaintenance,
            notifiable: fn () => $booking->user
        );
    }

    /**
     * Tukar unit kendaraan pada rental yang sedang berjalan (confirmed/ongoing).
     */
    public function swapForRental(
        Rental $rental,
        Vehicle $replacementVehicle,
        User $actor,
        string $reason = '',
        ?float $priceDifference = null,
        bool $markMaintenance = true
    ): VehicleReplacement {
        return $this->execute(
            subject: 'rental',
            model: $rental,
            currentVehicleId: $rental->vehicle_id,
            isActive: fn () => in_array($rental->status, ['confirmed', 'ongoing']),
            newUnitStatus: $rental->status === 'ongoing' ? 'rented' : 'reserved',
            applySwap: function (Vehicle $newVehicle) use ($rental, $priceDifference) {
                $totalAmount = max(0, (float) $rental->total_amount + (float) ($priceDifference ?? 0));

                $rental->update([
                    'vehicle_id' => $newVehicle->id,
                    'total_amount' => $totalAmount,
                ]);
            },
            replacementVehicle: $replacementVehicle,
            actor: $actor,
            reason: $reason,
            priceDifference: $priceDifference,
            markMaintenance: $markMaintenance,
            notifiable: fn () => $rental->user
        );
    }

    private function execute(
        string $subject,
        $model,
        ?int $currentVehicleId,
        callable $isActive,
        string $newUnitStatus,
        callable $applySwap,
        Vehicle $replacementVehicle,
        User $actor,
        string $reason,
        ?float $priceDifference,
        bool $markMaintenance,
        callable $notifiable
    ): VehicleReplacement {
        if (! $currentVehicleId) {
            throw ValidationException::withMessages([
                'replacement_vehicle_id' => 'Sewa ini tidak memiliki kendaraan.',
            ]);
        }

        if (! $isActive()) {
            throw ValidationException::withMessages([
                'status' => 'Penggantian hanya untuk sewa yang aktif (confirmed/ongoing).',
            ]);
        }

        if ((int) $replacementVehicle->id === (int) $currentVehicleId) {
            throw ValidationException::withMessages([
                'replacement_vehicle_id' => 'Kendaraan pengganti harus berbeda dari kendaraan saat ini.',
            ]);
        }

        return DB::transaction(function () use (
            $subject, $model, $currentVehicleId, $newUnitStatus, $applySwap,
            $replacementVehicle, $actor, $reason, $priceDifference, $markMaintenance, $notifiable
        ) {
            $oldVehicle = Vehicle::whereKey($currentVehicleId)->lockForUpdate()->first();
            $newVehicle = Vehicle::whereKey($replacementVehicle->id)->lockForUpdate()->first();

            if (! $newVehicle || $newVehicle->status !== 'available' || ! $newVehicle->is_active) {
                throw ValidationException::withMessages([
                    'replacement_vehicle_id' => 'Kendaraan pengganti tidak tersedia.',
                ]);
            }

            if ((int) $newVehicle->category_id !== (int) $oldVehicle->category_id) {
                throw ValidationException::withMessages([
                    'replacement_vehicle_id' => sprintf(
                        'Kendaraan pengganti harus dalam kategori yang sama (%s).',
                        $oldVehicle->category?->name ?? 'kategori unit lama'
                    ),
                ]);
            }

            // Unit lama keluar dari sirkulasi (maintenance) atau langsung tersedia kembali.
            $oldVehicle->update([
                'status' => $markMaintenance ? 'maintenance' : 'available',
            ]);

            $newVehicle->update(['status' => $newUnitStatus]);

            $applySwap($newVehicle);

            $replacement = VehicleReplacement::create([
                'booking_id' => $subject === 'booking' ? $model->id : null,
                'rental_id' => $subject === 'rental' ? $model->id : null,
                'original_vehicle_id' => $oldVehicle->id,
                'replacement_vehicle_id' => $newVehicle->id,
                'requested_by' => $actor->id,
                'approved_by' => $actor->id,
                'status' => 'approved',
                'swapped_at' => now(),
                'reason' => $reason !== '' ? $reason : 'Penggantian langsung oleh operator',
                'price_difference' => (float) ($priceDifference ?? 0),
            ]);

            $recipient = $notifiable();
            if ($recipient) {
                $recipient->notify(new VehicleReplaced($replacement));
            }

            return $replacement;
        });
    }
}
