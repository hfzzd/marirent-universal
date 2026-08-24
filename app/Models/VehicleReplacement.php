<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'rental_id', 'original_vehicle_id', 'replacement_vehicle_id',
        'requested_by', 'approved_by', 'status', 'swapped_at', 'reason',
        'admin_notes', 'price_difference',
    ];

    protected function casts(): array
    {
        return [
            'price_difference' => 'decimal:2',
            'swapped_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function originalVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'original_vehicle_id');
    }

    public function replacementVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'replacement_vehicle_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
