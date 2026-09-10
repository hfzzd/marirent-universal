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

    /**
     * Return the company profile for an owner, creating it from the merchant
     * profile when an older account does not have one yet.
     */
    public static function ensureForOwner(User|int $owner): self
    {
        $ownerId = $owner instanceof User ? $owner->id : $owner;
        $ownerUser = User::findOrFail($ownerId);

        if ($ownerUser->role === 'admin' && $ownerUser->owner_id) {
            $ownerId = (int) $ownerUser->owner_id;
            $ownerUser = User::findOrFail($ownerId);
        }

        $company = static::withTrashed()->where('user_id', $ownerId)->first();
        if ($company) {
            if ($company->trashed()) {
                $company->restore();
            }

            return $company;
        }

        $merchant = Merchant::withTrashed()->where('user_id', $ownerId)->first();
        $name = $merchant?->name ?: ('Company ' . $ownerUser->name);
        $slugBase = Str::slug($merchant?->slug ?: $name) ?: ('company-' . $ownerId);
        $slug = $slugBase;
        $suffix = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $suffix++;
        }

        $data = $merchant
            ? $merchant->only([
                'description', 'logo', 'banner', 'phone', 'company_email',
                'website', 'instagram', 'address', 'city', 'pickup_address',
                'operational_hours', 'commission_rate', 'is_active', 'status',
                'verified_at',
            ])
            : [
                'phone' => $ownerUser->phone,
                'company_email' => $ownerUser->email,
                'is_active' => true,
                'status' => 'pending',
                'commission_rate' => 0,
            ];

        return static::create(array_merge($data, [
            'user_id' => $ownerId,
            'slug' => $slug,
            'name' => $name,
        ]));
    }

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
