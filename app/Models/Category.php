<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'icon', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    public function cameras()
    {
        return $this->hasMany(Camera::class);
    }

    public function campingEquipments()
    {
        return $this->hasMany(CampingEquipment::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
