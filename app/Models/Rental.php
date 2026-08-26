<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rental extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rental_code',
        'user_id',
        'vehicle_id',
        'driver_id',
        'category_type',
        'start_date',
        'end_date',
        'actual_return',
        'pickup_location',
        'dropoff_location',
        'purpose',
        'with_driver',
        'status',
        'daily_rate',
        'total_days',
        'subtotal',
        'driver_fee',
        'discount',
        'tax',
        'total_amount',
        'notes',
        'cancelled_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'actual_return' => 'datetime',
            'with_driver' => 'boolean',
            'daily_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'driver_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Rental $rental) {
            if (empty($rental->rental_code)) {
                $rental->rental_code = static::generateRentalCode();
            }
        });
    }

    /**
     * Generate a unique rental code in MR-YYYYMMDD-XXXX format.
     */
    public static function generateRentalCode(): string
    {
        $datePrefix = 'MR-'.now()->format('Ymd').'-';
        $lastRental = static::where('rental_code', 'like', $datePrefix.'%')
            ->orderByDesc('rental_code')
            ->first();

        if ($lastRental) {
            $lastNumber = (int) substr($lastRental->rental_code, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $datePrefix.$nextNumber;
    }

    /**
     * Get the user that owns the rental.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vehicle for the rental.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver for the rental.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the inspection for the rental.
     */
    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }

    /**
     * Get the trip report for the rental.
     */
    public function tripReport(): HasOne
    {
        return $this->hasOne(TripReport::class);
    }

    /**
     * Get all vehicle replacements for the rental.
     */
    public function vehicleReplacements(): HasMany
    {
        return $this->hasMany(VehicleReplacement::class);
    }

    /**
     * Get the HP rental detail for the rental.
     */
    public function hpRental(): HasOne
    {
        return $this->hasOne(HpRental::class);
    }

    /**
     * Get the camera rental detail for the rental.
     */
    public function cameraRental(): HasOne
    {
        return $this->hasOne(CameraRental::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by category type.
     */
    public function scopeByCategory(Builder $query, string $categoryType): Builder
    {
        return $query->where('category_type', $categoryType);
    }
}
