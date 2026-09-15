<?php

namespace App\Models;

use App\Support\Phone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Merchant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'slug', 'name', 'description', 'logo', 'banner',
        'phone', 'company_email', 'website', 'instagram',
        'address', 'city', 'pickup_address', 'operational_hours',
        'latitude', 'longitude',
        'commission_rate', 'is_active', 'status',
        'verified_at', 'billing_plan', 'subscription_fee', 'subscription_until',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'subscription_fee' => 'decimal:2',
            'subscription_until' => 'datetime',
            'is_active' => 'boolean',
            'verified_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function mapsEmbedUrl(): string
    {
        if ($this->hasCoordinates()) {
            return 'https://www.google.com/maps?q=' . $this->latitude . ',' . $this->longitude . '&z=16&output=embed';
        }
        $query = implode(', ', array_filter([$this->address, $this->city]));
        return 'https://www.google.com/maps?q=' . urlencode($query) . '&z=15&output=embed';
    }

    public function mapsDirectionsUrl(): string
    {
        if ($this->hasCoordinates()) {
            return 'https://www.google.com/maps/dir/?api=1&destination=' . $this->latitude . ',' . $this->longitude;
        }
        $query = implode(', ', array_filter([$this->address, $this->city]));
        return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($query);
    }

    public function isVerified(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = Phone::normalize($value);
    }

    public function setCompanyEmailAttribute(?string $value): void
    {
        $this->attributes['company_email'] = $value === null ? null : strtolower(trim($value));
    }

    public function subscriptions()
    {
        return $this->hasMany(MerchantSubscription::class)->orderByDesc('period_start');
    }

    public function activeSubscription()
    {
        return $this->hasOne(MerchantSubscription::class)->whereNot('status', 'paid')->latestOfMany('period_start');
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
