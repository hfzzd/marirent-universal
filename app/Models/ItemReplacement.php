<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'item_type', 'original_item_id', 'replacement_item_id',
        'requested_by', 'approved_by', 'status', 'reason',
        'admin_notes', 'price_difference',
        'initial_item_photo', 'final_item_photo',
    ];

    protected function casts(): array
    {
        return [
            'price_difference' => 'decimal:2',
        ];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function requestedBy() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }

    public function originalItem()
    {
        return match ($this->item_type) {
            'hp' => $this->belongsTo(Phone::class, 'original_item_id'),
            'camera' => $this->belongsTo(Camera::class, 'original_item_id'),
            'tenda' => $this->belongsTo(CampingEquipment::class, 'original_item_id'),
            'ps' => $this->belongsTo(Playstation::class, 'original_item_id'),
            'drone' => $this->belongsTo(Drone::class, 'original_item_id'),
            'musik' => $this->belongsTo(MusicalInstrument::class, 'original_item_id'),
            default => $this->belongsTo(Phone::class, 'original_item_id'),
        };
    }

    public function replacementItem()
    {
        return match ($this->item_type) {
            'hp' => $this->belongsTo(Phone::class, 'replacement_item_id'),
            'camera' => $this->belongsTo(Camera::class, 'replacement_item_id'),
            'tenda' => $this->belongsTo(CampingEquipment::class, 'replacement_item_id'),
            'ps' => $this->belongsTo(Playstation::class, 'replacement_item_id'),
            'drone' => $this->belongsTo(Drone::class, 'replacement_item_id'),
            'musik' => $this->belongsTo(MusicalInstrument::class, 'replacement_item_id'),
            default => $this->belongsTo(Phone::class, 'replacement_item_id'),
        };
    }
}
