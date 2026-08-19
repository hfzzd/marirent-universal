<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
