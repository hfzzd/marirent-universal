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
        'avatar', 'role', 'is_active',
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
    public function isOwner(): bool { return $this->role === 'owner'; }
    public function isUser(): bool { return $this->role === 'user'; }
    public function isDriver(): bool { return $this->role === 'driver'; }
    public function isInspector(): bool { return $this->role === 'inspector'; }

    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'inspector_id');
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
