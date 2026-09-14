<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantSubscription extends Model
{
    use HasFactory;

    protected $table = 'merchant_subscriptions';

    protected $fillable = [
        'merchant_id', 'period_start', 'period_end', 'amount',
        'status', 'method', 'reference_number', 'proof_photo',
        'notes', 'rejection_reason', 'paid_at', 'verified_by', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}