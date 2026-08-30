<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'vehicle_id', 'inspector_id', 'reported_by', 'assigned_to',
        'type', 'scope', 'status',
        'item_type', 'item_id',
        'exterior_condition', 'interior_condition', 'engine_condition',
        'tire_condition', 'brake_condition', 'electrical_condition',
        'overall_condition', 'fuel_level', 'odometer_reading',
        'usage_duration_hours',
        'damages', 'damage_items', 'completeness', 'photos',
        'notes', 'recommendations', 'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'fuel_level' => 'decimal:2',
            'odometer_reading' => 'decimal:2',
            'usage_duration_hours' => 'integer',
            'damages' => 'array',
            'damage_items' => 'array',
            'completeness' => 'array',
            'photos' => 'array',
        ];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function inspector() { return $this->belongsTo(User::class, 'inspector_id'); }
    public function reportedBy() { return $this->belongsTo(User::class, 'reported_by'); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }

    public function canBeProcessedBy(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isMerchantStaff() || $user->isInspector()) {
            return $this->isOwnedByMerchant($user);
        }
        return false;
    }

    public function isOwnedByMerchant(User $user): bool
    {
        $merchantId = $user->merchantId();

        if ($this->vehicle && $this->vehicle->owner_id) {
            return $this->vehicle->owner_id == $merchantId;
        }

        if ($this->booking?->user_id) {
            return true;
        }

        return false;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'open' => 'Menunggu Laporan',
            'reported' => 'Laporan Driver',
            'processing' => 'Dikerjakan Inspector',
            'completed' => 'Selesai',
            default => ucfirst($this->status ?? 'open'),
        };
    }

    public function hasDamages(): bool
    {
        return !empty($this->damage_items) && count($this->damage_items) > 0;
    }

    public function getConditionLabel(): string
    {
        if (!$this->overall_condition) return '-';
        return match(true) {
            $this->overall_condition >= 8 => 'Sangat Baik',
            $this->overall_condition >= 6 => 'Baik',
            $this->overall_condition >= 4 => 'Cukup',
            default => 'Buruk',
        };
    }

    public function getItemName(): string
    {
        if ($this->vehicle) return $this->vehicle->name;

        if ($this->item_type && $this->item_id) {
            $model = $this->item_type;
            $item = $model::find($this->item_id);
            if ($item) return $item->name ?? '-';
        }

        return '-';
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'pre_rental' => 'Inspeksi Awal',
            'post_rental' => 'Inspeksi Akhir',
            'periodic' => 'Periodik',
            default => ucfirst($this->type),
        };
    }

    public function getScopeLabel(): string
    {
        return match($this->scope) {
            'kendaraan' => 'Kendaraan',
            'elektronik' => 'Elektronik',
            'camping' => 'Alat Camping',
            default => ucfirst($this->scope ?? 'kendaraan'),
        };
    }

    public function getUsageDurationLabel(): string
    {
        if (!$this->usage_duration_hours) return '-';
        $hours = $this->usage_duration_hours;
        if ($hours < 24) return $hours . ' jam';
        $days = floor($hours / 24);
        $remainingHours = $hours % 24;
        return $days . ' hari' . ($remainingHours > 0 ? ' ' . $remainingHours . ' jam' : '');
    }
}
