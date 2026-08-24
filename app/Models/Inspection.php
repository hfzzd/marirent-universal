<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    public const DAMAGE_OPTIONS = [
        'kendaraan' => [
            'Baret / Cat Tergores',
            'Penyok / Bengkok',
            'Kaca Pecah / Retak',
            'Ban Gundul / Kempis',
            'Lampu / Lampu Sen Mati',
            'Rem Bunyi / Tidak Prima',
            'AC Tidak Dingin',
            'Mesin Kurang Prima',
            'Interior Kotor / Sobek',
            'Spion Pecah / Hilang',
            'Aki / Kelistrikan Lemah',
            'Bau Tidak Sedap di Kabin',
        ],
        'elektronik' => [
            'Layar Gores / Pecah',
            'Body Baret / Penyok',
            'Tombol / Port Rusak',
            'Lensa Gores / Berjamur',
            'Flash Rusak',
            'Battery Health Turun',
            'Charger / Kabel Hilang',
            'Memory Card Hilang',
            'Terkena Air (Water Damage)',
            'Software Error / Restart Sendiri',
        ],
        'camping' => [
            'Tenda Robek / Bocor',
            'Pasak Hilang / Rusak',
            'Frame / Tiang Bengkok',
            'Zipper Rusak / Macet',
            'Flysheet Rusak',
            'Tali Rangka Putus',
            'Karimat Hilang / Bocor',
            'Kotor / Berlumut',
            'Tas Carrying Rusak',
            'Bau apek / Lembap',
        ],
    ];

    public const COMPLETENESS_OPTIONS = [
        'elektronik' => [
            'hp' => ['Unit HP', 'Charger', 'Kabel Data', 'Box Original', 'Casing', 'Earphone', 'Memory Card', 'SIM Tool'],
            'kamera' => ['Body Kamera', 'Lensa Kit', 'Baterai', 'Charger', 'Memory Card', 'Strap', 'Tas Kamera', 'Cleaning Kit', 'Tripod'],
        ],
        'camping' => [
            'default' => ['Tenda Utama', 'Flysheet', 'Frame / Tiang', 'Pasak Set', 'Tali Rangka', 'Karimat', 'Rainfly Tambahan', 'Tas Carrying'],
        ],
    ];

    protected $fillable = [
        'booking_id', 'vehicle_id', 'item_type', 'item_id', 'inspector_id',
        'type', 'scope',
        'exterior_condition', 'interior_condition', 'engine_condition',
        'tire_condition', 'brake_condition', 'electrical_condition',
        'overall_condition', 'fuel_level', 'odometer_reading',
        'usage_duration_hours',
        'damages', 'damage_items', 'completeness', 'photos',
        'notes', 'recommendations',
    ];

    protected function casts(): array
    {
        return [
            'fuel_level' => 'decimal:2',
            'odometer_reading' => 'decimal:2',
            'damages' => 'array',
            'damage_items' => 'array',
            'completeness' => 'array',
            'photos' => 'array',
        ];
    }

    public function booking() { return $this->belongsTo(Booking::class); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function inspector() { return $this->belongsTo(User::class, 'inspector_id'); }
    public function item() { return $this->morphTo(); }

    public function getItemName(): string
    {
        if ($this->vehicle) {
            return $this->vehicle->name;
        }

        return $this->item?->name ?? $this->booking?->category?->name ?? '-';
    }

    public function getScopeLabel(): string
    {
        return match ($this->scope) {
            'elektronik' => 'Elektronik (HP / Kamera)',
            'camping' => 'Alat Camping (Tenda)',
            default => 'Kendaraan',
        };
    }

    public function getTypeLabel(): string
    {
        return $this->type === 'pre_rental' ? 'Inspeksi Awal' : 'Inspeksi Akhir';
    }

    public function hasDamages(): bool
    {
        return ($this->damage_items && count($this->damage_items) > 0)
            || ($this->damages && count($this->damages) > 0);
    }

    public function getConditionLabel(): string
    {
        return match(true) {
            $this->overall_condition >= 8 => 'Sangat Baik',
            $this->overall_condition >= 6 => 'Baik',
            $this->overall_condition >= 4 => 'Cukup',
            default => 'Buruk',
        };
    }

    public function getUsageDurationLabel(): string
    {
        if (!$this->usage_duration_hours) {
            return '-';
        }

        $days = intdiv($this->usage_duration_hours, 24);
        $hours = $this->usage_duration_hours % 24;

        if ($days > 0 && $hours > 0) {
            return "{$days} hari {$hours} jam";
        }
        if ($days > 0) {
            return "{$days} hari";
        }

        return "{$hours} jam";
    }
}
