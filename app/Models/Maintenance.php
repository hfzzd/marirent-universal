<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'maintenance_code', 'title', 'description', 'vehicle_id', 'scheduled_date', 'completed_date',
        'type', 'status', 'priority', 'estimated_cost', 'actual_cost',
        'technician', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'datetime',
            'completed_date' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
        ];
    }

    public static function generateMaintenanceCode(): string
    {
        return 'MTN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
