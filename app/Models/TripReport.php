<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'driver_id', 'vehicle_id', 'start_odometer',
        'end_odometer', 'total_distance', 'fuel_used', 'fuel_cost',
        'toll_cost', 'parking_cost', 'other_cost', 'total_operational_cost',
        'route_points', 'photos', 'notes', 'issues_reported', 'status',
        'photo_front', 'photo_rear', 'photo_right', 'photo_left',
    ];

    protected function casts(): array
    {
        return [
            'start_odometer' => 'decimal:2', 'end_odometer' => 'decimal:2',
            'total_distance' => 'decimal:2', 'fuel_used' => 'decimal:2',
            'fuel_cost' => 'decimal:2', 'toll_cost' => 'decimal:2',
            'parking_cost' => 'decimal:2', 'other_cost' => 'decimal:2',
            'total_operational_cost' => 'decimal:2',
            'route_points' => 'array', 'photos' => 'array',
        ];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }

    public function calculateTotalCost(): float
    {
        $total = $this->fuel_cost + $this->toll_cost + $this->parking_cost + $this->other_cost;
        $this->total_operational_cost = $total;
        $this->save();
        return $total;
    }
}
