<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'vehicle_id', 'inspector_id', 'type',
        'exterior_condition', 'interior_condition', 'engine_condition',
        'tire_condition', 'brake_condition', 'electrical_condition',
        'overall_condition', 'fuel_level', 'odometer_reading',
        'damages', 'photos', 'notes', 'recommendations',
    ];

    protected function casts(): array
    {
        return [
            'fuel_level' => 'decimal:2',
            'odometer_reading' => 'decimal:2',
            'damages' => 'array',
            'photos' => 'array',
        ];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function inspector() { return $this->belongsTo(User::class, 'inspector_id'); }

    public function hasDamages(): bool
    {
        return !empty($this->damages) && count($this->damages) > 0;
    }

    public function getConditionLabel(): string
    {
        return match(true) {
            $this->overall_condition >= 8 => 'Sangat Baik',
            $this->overall_condition >= 6 => 'Baik',
            $this->overall_condition >= 4 => 'Cukup',
            default => 'Buruk',
        };
    }
}
