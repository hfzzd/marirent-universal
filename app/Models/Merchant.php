<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Merchant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'slug', 'name', 'description', 'logo', 'banner',
        'phone', 'address', 'city', 'pickup_address', 'operational_hours',
        'commission_rate', 'is_active', 'status',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Merchant $merchant) {
            if (empty($merchant->slug)) {
                $merchant->slug = Str::slug($merchant->name) ?: Str::random(6);
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'owner_id', 'user_id');
    }

    public function admins()
    {
        return $this->hasMany(User::class, 'owner_id', 'user_id')->where('role', 'admin');
    }

    public function getAverageRating(): float
    {
        $vehicles = $this->vehicles()->with('reviews')->get();
        $all = $vehicles->flatMap(fn($v) => $v->reviews);
        if ($all->isEmpty()) {
            return 0;
        }
        return round($all->avg('rating'), 1);
    }

    public function getReviewCount(): int
    {
        return $this->vehicles()->with('reviews')->get()->flatMap(fn($v) => $v->reviews)->count();
    }
}
