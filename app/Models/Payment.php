<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_code', 'invoice_id', 'user_id', 'amount', 'method',
        'reference_number', 'proof_photo', 'status', 'notes', 'paid_at',
        'verified_by', 'verified_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public static function generatePaymentCode(): string
    {
        return 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function verifiedBy() { return $this->belongsTo(User::class, 'verified_by'); }
}
