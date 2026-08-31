<?php

namespace App\Models\Concerns;

use App\Models\Scopes\MerchantScope;

/**
 * Menerapkan isolasi tenant (merchant) pada model yang memiliki kolom `owner_id`.
 *
 * Scope aktif hanya ketika pengguna terautentikasi adalah bagian dari merchant
 * (staff/owner, driver, inspector) dan bukan sedang membuka halaman publik marketplace.
 * Superadmin (platform) dan pengunjung tidak dibatasi.
 */
trait TenantIsolatable
{
    public static function bootTenantIsolatable(): void
    {
        static::addGlobalScope(new MerchantScope);
    }
}
