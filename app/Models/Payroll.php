<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'driver_id',
        'period_start',
        'period_end',
        'base_salary',
        'commission',
        'bonus',
        'deduction',
        'total_trips',
        'total_amount',
        'status',
        'paid_date',
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
            'base_salary' => 'decimal:2',
            'commission' => 'decimal:2',
            'bonus' => 'decimal:2',
            'deduction' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
            'paid_date' => 'date',
        ];
    }

    /**
     * Get the driver that owns the payroll record.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
}
