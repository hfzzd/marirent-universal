<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address',
        'avatar', 'role', 'owner_id', 'category_id', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isOwner(): bool { return $this->role === 'owner'; }
    public function isUser(): bool { return $this->role === 'user'; }
    public function isDriver(): bool { return $this->role === 'driver'; }
    public function isInspector(): bool { return $this->role === 'inspector'; }
    public function isMerchantStaff(): bool { return in_array($this->role, ['admin', 'owner']); }

    /**
     * Identitas platform / developer aplikasi (superadmin).
     * Bisa mengakses seluruh data & mengelola marketplace.
     */
    public function isPlatformAdmin(): bool { return $this->role === 'superadmin'; }

    /**
     * Relasi ke profil merchant (toko/company) milik owner.
     * Owner = merchant itu sendiri; admin/driver di bawah owner.
     */
    public function merchantProfile()
    {
        return $this->hasOne(Merchant::class, 'user_id');
    }

    public function merchants()
    {
        return $this->hasMany(Merchant::class, 'user_id');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function merchantCategory()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function merchantOwner()
    {
        if ($this->role === 'owner') {
            return $this;
        }
        if ($this->role === 'admin' && $this->owner_id) {
            return $this->merchant;
        }
        return null;
    }

    public function merchantId(): ?int
    {
        $owner = $this->merchantOwner();
        return $owner?->id;
    }

    /**
     * Id merchant (user owner) yang berhak mengelola data.
     * Untuk driver, merujuk ke owner pemilik driver.
     */
    public function merchantIdForIsolation(): ?int
    {
        if ($this->isMerchantStaff()) {
            return $this->merchantId();
        }

        if ($this->isDriver()) {
            $driver = $this->driverProfile;
            return $driver?->owner_id;
        }

        if ($this->isInspector()) {
            return $this->merchantId();
        }

        return null;
    }

    /**
     * Kategori produk yang dikelola admin/owner (null = seluruh merchant).
     * Hanya berlaku untuk role admin & owner.
     */
    public function merchantCategoryId(): ?int
    {
        if (!$this->isMerchantStaff()) {
            return null;
        }

        return $this->category_id ? (int) $this->category_id : null;
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function driverProfile()
    {
        return $this->hasOne(Driver::class);
    }

    public function ownedDrivers()
    {
        return $this->hasMany(Driver::class, 'owner_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
