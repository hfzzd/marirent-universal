<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraRental extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rental_id',
        'camera_brand',
        'camera_model',
        'lens_included',
        'accessories',
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
            'accessories' => 'array',
        ];
    }

    /**
     * Get the rental that owns the camera rental.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
