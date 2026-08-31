<?php

namespace App\Models;

use App\Models\Concerns\TenantIsolatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, TenantIsolatable;

    public const TENANT_COLUMN = 'owner_id';

    protected $fillable = [
        'category_id', 'owner_id', 'name', 'slug', 'brand', 'model',
        'year', 'color', 'license_plate', 'description', 'daily_price',
        'weekly_price', 'monthly_price', 'hourly_price', 'with_driver_daily_price',
        'image', 'gallery', 'status', 'condition', 'seats', 'transmission',
        'fuel_type', 'mileage', 'with_driver', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_price' => 'decimal:2', 'weekly_price' => 'decimal:2',
            'monthly_price' => 'decimal:2', 'hourly_price' => 'decimal:2',
            'with_driver_daily_price' => 'decimal:2',
            'gallery' => 'array', 'is_active' => 'boolean', 'with_driver' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function tripReports()
    {
        return $this->hasMany(TripReport::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'item');
    }

    public function getPriceForType(string $type): float
    {
        return match ($type) {
            'hourly' => $this->hourly_price ?? 0,
            'daily' => $this->daily_price,
            'weekly' => $this->weekly_price ?? ($this->daily_price * 7),
            'monthly' => $this->monthly_price ?? ($this->daily_price * 30),
            default => $this->daily_price,
        };
    }

    public function getAverageRating(): float
    {
        return (float) ($this->morphMany(Review::class, 'item')->avg('rating') ?? 0);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
