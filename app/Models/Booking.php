<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_code', 'user_id', 'vehicle_id', 'driver_id', 'category_id',
        'item_type', 'item_id',
        'rental_type', 'start_date', 'end_date', 'actual_start_date',
        'actual_end_date', 'pickup_location', 'dropoff_location', 'with_driver',
        'base_price', 'driver_price', 'total_price', 'discount', 'final_price',
        'status', 'payment_status', 'payment_due_date', 'source',
        'notes', 'cancellation_reason', 'ktp_photo',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime', 'end_date' => 'datetime',
            'actual_start_date' => 'datetime', 'actual_end_date' => 'datetime',
            'base_price' => 'decimal:2', 'driver_price' => 'decimal:2',
            'total_price' => 'decimal:2', 'discount' => 'decimal:2',
            'final_price' => 'decimal:2', 'with_driver' => 'boolean',
        ];
    }

    public static function generateBookingCode(): string
    {
        return 'MR-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -6));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function item()
    {
        return $this->morphTo();
    }

    public function inspection()
    {
        return $this->hasOne(Inspection::class);
    }

    public function tripReport()
    {
        return $this->hasOne(TripReport::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'booking_invoice')->withTimestamps();
    }

    public function replacements()
    {
        return $this->hasMany(VehicleReplacement::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class);
    }

    public function getDuration(): int
    {
        return $this->start_date->diffInDays($this->end_date);
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
}
