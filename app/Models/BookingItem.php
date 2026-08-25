<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'item_type', 'item_id', 'rental_type',
        'unit_price', 'quantity', 'subtotal', 'accessories',
        'with_insurance', 'insurance_fee',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
            'accessories' => 'array',
            'with_insurance' => 'boolean',
            'insurance_fee' => 'decimal:2',
        ];
    }

    public function booking() { return $this->belongsTo(Booking::class); }

    public function item()
    {
        return $this->morphTo();
    }

    public function getItemName(): string
    {
        return $this->item?->name ?? '-';
    }
}
