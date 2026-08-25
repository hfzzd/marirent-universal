<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'user_id', 'item_type', 'item_id', 'rating', 'comment', 'is_visible'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'is_visible' => 'boolean'];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi polymorphic ke item yang diulas (Vehicle, Phone, Camera, CampingEquipment).
     */
    public function reviewable()
    {
        return $this->morphTo('item');
    }
}
