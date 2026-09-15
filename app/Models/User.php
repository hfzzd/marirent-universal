<?php

namespace App\Models;

use App\Support\Phone;
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

    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = $value === null ? null : strtolower(trim($value));
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = Phone::normalize($value);
    }

    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isOwner(): bool { return $this->role === 'owner'; }
    public function isUser(): bool { return $this->role === 'user'; }
    public function isDriver(): bool { return $this->role === 'driver'; }
    public function isStaff(): bool { return $this->role === 'staff'; }
    /** Sopir maupun staff operasional (akses operasional setara driver). */
    public function isDriverOrStaff(): bool { return in_array($this->role, ['driver', 'staff'], true); }
    public function isEmployee(): bool { return $this->role === 'employee'; }
    public function isInspector(): bool { return $this->role === 'inspector'; }
    public function isMerchantStaff(): bool { return in_array($this->role, ['admin', 'owner']); }

    public static function roleForPosition(?string $position, ?int $categoryId = null): string
    {
        if ($categoryId && !Category::whereKey($categoryId)->whereIn('slug', ['mobil', 'motor'])->exists()) {
            return 'staff';
        }

        $position = strtolower(trim((string) $position));

        if ($position === '') {
            return 'driver';
        }

        if (preg_match('/\b(non[- ]driver|bukan\s+driver)\b/', $position)) {
            return 'staff';
        }

        return preg_match('/\b(driver|supir|pengemudi)\b/', $position) === 1
            ? 'driver'
            : 'staff';
    }

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

    /**
     * Alias "company" untuk profil merchant/toko pemilik.
     * Mengembalikan model Company (tabel merchants).
     */
    public function company()
    {
        return $this->hasOne(Company::class, 'user_id');
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
        if (in_array($this->role, ['admin', 'staff', 'employee'], true) && $this->owner_id) {
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

        if ($this->isDriver() || $this->isEmployee() || $this->isStaff()) {
            $ownerId = \App\Models\Driver::withoutGlobalScopes()
                ->where('user_id', $this->id)
                ->value('owner_id');
            if ($ownerId) {
                return (int) $ownerId;
            }
            // Staff/employee tanpa baris driver: ikut merchant via owner_id akunnya.
            if (($this->isStaff() || $this->isEmployee()) && $this->owner_id) {
                return (int) $this->owner_id;
            }
            return $ownerId ? (int) $ownerId : null;
        }

        if ($this->isInspector()) {
            return $this->owner_id ? (int) $this->owner_id : null;
        }

        return null;
    }

    /**
     * Kategori produk yang dikelola admin/owner (null = seluruh merchant).
     * Hanya berlaku untuk role admin, owner, employee & staff.
     */
    public function merchantCategoryId(): ?int
    {
        if (!$this->isMerchantStaff() && !$this->isEmployee() && !$this->isStaff()) {
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
