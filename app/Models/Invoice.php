<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'booking_id', 'user_id', 'owner_id', 'type',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount',
        'paid_amount', 'due_amount', 'status', 'payment_method',
        'payment_reference', 'paid_at', 'due_date', 'notes', 'terms',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2', 'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2', 'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2', 'due_amount' => 'decimal:2',
            'paid_at' => 'datetime', 'due_date' => 'datetime',
        ];
    }

    public static function generateInvoiceNumber(string $type): string
    {
        $prefix = match($type) {
            'rental' => 'INV-R',
            'driver_salary' => 'INV-S',
            'replacement' => 'INV-P',
            'damage' => 'INV-D',
            default => 'INV-O',
        };
        return $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function bookings() { return $this->belongsToMany(Booking::class, 'booking_invoice')->withTimestamps(); }
    public function user() { return $this->belongsTo(User::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function items() { return $this->hasMany(InvoiceItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function getRemainingAmount(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Sinkronkan payment_status booking terkait dengan kondisi invoice ini,
     * agar datasheet/monitoring selalu mencerminkan pembayaran terbaru.
     */
    public function syncBookingPaymentStatus(): void
    {
        $bookings = $this->bookings;
        if ($bookings->isEmpty() && $this->booking) {
            $bookings = collect([$this->booking]);
        }

        foreach ($bookings as $b) {
            if (!$b || $b->payment_status === 'refunded') {
                continue;
            }
            if ($this->status === 'paid') {
                $b->update(['payment_status' => 'paid']);
            } elseif ((float) $this->paid_amount > 0 && $b->payment_status !== 'partial') {
                $b->update(['payment_status' => 'partial']);
            } elseif ((float) $this->paid_amount <= 0 && $b->payment_status === 'partial') {
                $b->update(['payment_status' => 'unpaid']);
            }
        }
    }

    /**
     * Hitung ulang paid/due/status dari seluruh payment verified lalu
     * sinkronkan ke booking.
     */
    public function recalcFromPayments(): void
    {
        $paid = min((float) $this->payments()->where('status', 'verified')->sum('amount'), (float) $this->total_amount);

        $this->update([
            'paid_amount' => $paid,
            'due_amount' => max(0, (float) $this->total_amount - $paid),
            'status' => $paid >= (float) $this->total_amount && $paid > 0 ? 'paid' : ($paid > 0 ? 'partial' : ($this->status === 'draft' ? 'draft' : 'sent')),
            'paid_at' => $paid > 0 ? ($this->paid_at ?? now()) : null,
        ]);

        $this->syncBookingPaymentStatus();
    }
}
