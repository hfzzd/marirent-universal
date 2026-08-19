<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'owner_id', 'name', 'slug', 'brand', 'phone_model',
        'storage_capacity', 'ram', 'color', 'description',
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
    public function reviews() { return $this->morphMany(Review::class, 'reviewable'); }

    public function getAverageRating(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
