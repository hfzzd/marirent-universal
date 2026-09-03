<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Model "company" (perusahaan/toko).
 *
 * Menggunakan tabel khusus `companies` yang berisi data perusahaan pemilik
 * merchant (role owner), terpisah dari tabel `merchants`.
 */
class Company extends Merchant
{
    protected $table = 'companies';

    public function profile()
    {
        return $this;
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'company_id');
    }

    public function phones()
    {
        return $this->hasMany(Phone::class, 'company_id');
    }

    public function cameras()
    {
        return $this->hasMany(Camera::class, 'company_id');
    }

    public function campingEquipments()
    {
        return $this->hasMany(CampingEquipment::class, 'company_id');
    }

    public function playstations()
    {
        return $this->hasMany(Playstation::class, 'company_id');
    }

    public function drones()
    {
        return $this->hasMany(Drone::class, 'company_id');
    }

    public function musicalInstruments()
    {
        return $this->hasMany(MusicalInstrument::class, 'company_id');
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class, 'owner_id', 'user_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'owner_id', 'user_id');
    }

    public function allProducts()
    {
        return $this->vehicles
            ->push(...$this->phones)
            ->push(...$this->cameras)
            ->push(...$this->campingEquipments)
            ->push(...$this->playstations)
            ->push(...$this->drones)
            ->push(...$this->musicalInstruments);
    }

    protected static function booted(): void
    {
        static::saving(function (Company $company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name) ?: Str::random(6);
            }
        });
    }
}
