<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampingEquipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'camping_equipments';

    protected $fillable = [
        'category_id', 'owner_id', 'name', 'slug', 'brand', 'equipment_model',
        'type', 'capacity', 'weight', 'material', 'description',
        'daily_price', 'weekly_price', 'monthly_price', 'hourly_price',
        'image', 'gallery', 'status', 'condition', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_price' => 'decimal:2', 'weekly_price' => 'decimal:2',
            'monthly_price' => 'decimal:2', 'hourly_price' => 'decimal:2',
            'gallery' => 'array', 'is_active' => 'boolean',
        ];
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }

    public function reviews()
    {
        $q = $this->hasMany(Review::class, 'vehicle_id');
        $q->whereRaw('1 = 0');
        return $q;
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

    public function getAverageRating(): float
    {
        return 0;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
