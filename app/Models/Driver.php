<?php

namespace App\Models;

use App\Models\Concerns\TenantIsolatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes, TenantIsolatable;

    public const TENANT_COLUMN = 'owner_id';

    protected $fillable = [
        'user_id', 'owner_id', 'company_id', 'position',
        'license_number', 'license_expiry',
        'license_type', 'daily_salary', 'trip_salary', 'status',
        'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'daily_salary' => 'decimal:2',
            'trip_salary' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function company() { return $this->belongsTo(Company::class, 'company_id'); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function tripReports() { return $this->hasMany(TripReport::class); }
    public function salaries() { return $this->hasMany(DriverSalary::class); }
    public function rentals() { return $this->hasMany(Rental::class); }
    public function payrolls() { return $this->hasMany(Payroll::class); }

    public function isAvailable(): bool
    {
        return $this->status === 'off_duty' && $this->is_active;
    }
}
