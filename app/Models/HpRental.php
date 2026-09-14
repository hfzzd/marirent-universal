<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HpRental extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rental_id',
        'phone_brand',
        'phone_model',
        'storage_capacity',
        'color',
        'daily_rate',
        'insurance_fee',
        'deposit',
        'condition_before',
        'condition_after',
        'hp_code',
        'vehicle_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'ic_number',
        'license_number',
        'hire_period_months',
        'monthly_payment',
        'down_payment',
        'interest_rate',
        'total_cost',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'insurance_fee' => 'decimal:2',
            'deposit' => 'decimal:2',
        ];
    }

    /**
     * Get the rental that owns the HP rental.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
