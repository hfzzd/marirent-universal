<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverSalary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'driver_id', 'owner_id', 'period_month', 'base_salary',
        'trip_bonus', 'overtime_pay', 'deductions', 'total_salary',
        'status', 'invoice_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2', 'trip_bonus' => 'decimal:2',
            'overtime_pay' => 'decimal:2', 'deductions' => 'decimal:2',
            'total_salary' => 'decimal:2',
        ];
    }

    public function driver() { return $this->belongsTo(Driver::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function invoice() { return $this->belongsTo(Invoice::class); }

    public function calculateTotal(): float
    {
        $this->total_salary = ($this->base_salary + $this->trip_bonus + $this->overtime_pay) - $this->deductions;
        return $this->total_salary;
    }
}
