<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    public const CATALOG_MODELS = [
        Vehicle::class,
        Phone::class,
        Camera::class,
        CampingEquipment::class,
        Playstation::class,
        Drone::class,
        MusicalInstrument::class,
    ];

    protected $fillable = [
        'booking_code', 'user_id', 'vehicle_id', 'driver_id', 'category_id',
        'item_type', 'item_id',
        'rental_type', 'start_date', 'end_date', 'actual_start_date',
        'actual_end_date', 'pickup_location', 'dropoff_location', 'with_driver',
        'base_price', 'driver_price', 'deposit_amount', 'insurance_fee',
        'total_price', 'discount', 'final_price',
        'status', 'payment_status', 'notes', 'cancellation_reason', 'ktp_photo',
        'accessories', 'urgency', 'with_insurance',
        'payment_plan', 'payment_due_date', 'source', 'parent_booking_id',
        'dp_amount',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime', 'end_date' => 'datetime',
            'actual_start_date' => 'datetime', 'actual_end_date' => 'datetime',
            'base_price' => 'decimal:2', 'driver_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2', 'insurance_fee' => 'decimal:2',
            'total_price' => 'decimal:2', 'discount' => 'decimal:2',
            'final_price' => 'decimal:2', 'with_driver' => 'boolean',
            'accessories' => 'array', 'with_insurance' => 'boolean',
            'dp_amount' => 'decimal:2',
        ];
    }

    public static function generateBookingCode(): string
    {
        return 'MR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function user() { return $this->belongsTo(User::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Driver::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function item() { return $this->morphTo(); }
    /** The most recent inspection, kept for existing callers that expect one model. */
    public function inspection() { return $this->hasOne(Inspection::class)->latestOfMany(); }
    public function inspections() { return $this->hasMany(Inspection::class)->latest(); }
    public function preInspection() { return $this->hasOne(Inspection::class)->where('type', 'pre_rental')->latestOfMany(); }
    public function postInspection() { return $this->hasOne(Inspection::class)->where('type', 'post_rental')->latestOfMany(); }
    public function tripReport() { return $this->hasOne(TripReport::class); }
    public function invoice() { return $this->hasOne(Invoice::class); }
    public function invoices() { return $this->belongsToMany(Invoice::class, 'booking_invoice'); }
    public function replacements() { return $this->hasMany(VehicleReplacement::class); }
    public function review() { return $this->hasOne(Review::class); }
    public function payments() { return $this->hasManyThrough(Payment::class, Invoice::class); }
    public function bookingItems() { return $this->hasMany(BookingItem::class); }
    public function parentBooking() { return $this->belongsTo(Booking::class, 'parent_booking_id'); }
    public function childBookings() { return $this->hasMany(Booking::class, 'parent_booking_id'); }

    public function isVehicleBooking(): bool
    {
        return $this->vehicle_id !== null;
    }

    public function getDuration(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    public function getDpAmount(): ?float
    {
        if ($this->payment_plan !== 'dp50') {
            return null;
        }

        if ($this->dp_amount !== null) {
            return round((float) $this->dp_amount);
        }

        return round((float) $this->final_price * 0.5);
    }

    public function setDpAmountFromPlan(): void
    {
        if ($this->payment_plan === 'dp50') {
            $dp = round((float) $this->final_price * 0.5);
            $this->update(['dp_amount' => $dp]);
        } else {
            $this->update(['dp_amount' => null]);
        }
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOwnedByMerchant($query, ?int $merchantId)
    {
        if (!$merchantId) {
            return $query;
        }

        $idsByType = [];
        foreach (self::CATALOG_MODELS as $model) {
            $idsByType[$model] = $model::where('owner_id', $merchantId)->pluck('id');
        }

        return $query->where(function ($q) use ($merchantId, $idsByType) {
            $q->whereHas('vehicle', fn($vq) => $vq->where('owner_id', $merchantId));
            foreach ($idsByType as $type => $ids) {
                $q->orWhere(fn($tq) => $tq->where('item_type', $type)->whereIn('item_id', $ids));
            }
        });
    }

    /**
     * Filter booking milik merchant, opsional dibatasi 1 kategori produk.
     */
    public function scopeForMerchantCategory($query, ?int $merchantId, ?int $categoryId = null)
    {
        $query->ownedByMerchant($merchantId);

        if ($categoryId) {
            $query->where(function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhereHas('childBookings', fn($cq) => $cq->where('category_id', $categoryId));
            });
        }

        return $query;
    }

    /**
     * Kategori produk yang disentuh booking ini (termasuk anak multi-item).
     */
    public function merchantCategoryIds(): array
    {
        $ids = collect([$this->category_id]);

        foreach ($this->childBookings as $child) {
            $ids->push($child->category_id);
        }

        return $ids->filter()->map(fn($id) => (int) $id)->unique()->values()->all();
    }

    /**
     * Apakah booking menyentuh kategori tertentu?
     */
    public function belongsToCategory(?int $categoryId): bool
    {
        if (!$categoryId) {
            return true;
        }

        if (!$this->relationLoaded('childBookings')) {
            $this->load('childBookings');
        }

        return in_array((int) $categoryId, $this->merchantCategoryIds(), true);
    }

    /**
     * Id merchant (owner) yang memiliki unit pada booking ini.
     */
    public function merchantOwnerId(): ?int
    {
        if ($this->vehicle && $this->vehicle->owner_id) {
            return (int) $this->vehicle->owner_id;
        }

        if ($this->item && $this->item->owner_id) {
            return (int) $this->item->owner_id;
        }

        foreach ($this->childBookings as $child) {
            $id = $child->merchantOwnerId();
            if ($id) {
                return $id;
            }
        }

        return null;
    }
}
