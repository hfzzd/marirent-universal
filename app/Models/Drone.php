<?php

namespace App\Models;

use App\Models\Concerns\HasCompany;
use App\Models\Concerns\TenantIsolatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drone extends Model
{
    use HasFactory, SoftDeletes, TenantIsolatable, HasCompany;

    public const TENANT_COLUMN = 'owner_id';

    protected $fillable = [
        'category_id', 'owner_id', 'company_id', 'name', 'slug', 'brand', 'drone_model',
        'camera_resolution', 'flight_time', 'max_range', 'weight', 'accessories', 'description',
        'daily_price', 'weekly_price', 'monthly_price', 'hourly_price',
        'image', 'gallery', 'status', 'condition', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_price' => 'decimal:2', 'weekly_price' => 'decimal:2',
            'monthly_price' => 'decimal:2', 'hourly_price' => 'decimal:2',
            'gallery' => 'array', 'accessories' => 'array', 'is_active' => 'boolean',
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

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function getPriceForType(string $type): float
    {
        return match($type) {
            'hourly' => $this->hourly_price ?? 0,
            'daily' => $this->daily_price,
            'weekly' => $this->weekly_price ?? ($this->daily_price * 7),
            'monthly' => $this->monthly_price ?? ($this->daily_price * 30),
            default => $this->daily_price,
        };
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'item');
    }

    public function getAverageRating(): float
    {
        return (float) ($this->morphMany(Review::class, 'item')->avg('rating') ?? 0);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
