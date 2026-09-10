<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'rental_id', 'original_vehicle_id', 'replacement_vehicle_id',
        'requested_by', 'approved_by', 'status', 'reason',
        'admin_notes', 'price_difference',
        'mark_maintenance',
        'handover_type', 'handover_notes', 'actual_handover_at',
        'initial_vehicle_photo', 'final_vehicle_photo', 'swapped_at',
    ];

    protected function casts(): array
    {
        return [
            'price_difference' => 'decimal:2',
            'mark_maintenance' => 'boolean',
            'swapped_at' => 'datetime',
            'actual_handover_at' => 'datetime',
        ];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function originalVehicle() { return $this->belongsTo(Vehicle::class, 'original_vehicle_id'); }
    public function replacementVehicle() { return $this->belongsTo(Vehicle::class, 'replacement_vehicle_id'); }
    public function requestedBy() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }
}
