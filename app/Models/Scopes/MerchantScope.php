<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Scope global untuk isolasi antar merchant (company).
 *
 * Hanya membatasi query ketika:
 *  - ada pengguna terautentikasi yang merupakan bagian dari sebuah merchant (staff/owner/driver/inspector),
 *  - dan pengguna tersebut tidak sedang mengakses halaman publik marketplace.
 *
 * Superadmin (platform/developer) dan pengunjung (guest) tidak dibatasi.
 */
class MerchantScope implements Scope
{
    /**
     * Nama rute publik marketplace yang memperlihatkan seluruh merchant.
     */
    protected array $publicRoutes = [
        'home', 'about', 'products', 'contact', 'contact.submit',
        'public.vehicle', 'public.item', 'public.brands', 'public.brand',
        'public.store',
    ];

    public function apply(Builder $builder, Model $model): void
    {
        if (!defined($model::class . '::TENANT_COLUMN')) {
            return;
        }

        $column = constant($model::class . '::TENANT_COLUMN');
        $user = Auth::user();

        // Superadmin & guest (publik): tanpa pembatasan
        if (!$user) {
            return;
        }

        // Cegah recursion: scope pada model Driver yang sedang di-query oleh
        // user driver itu sendiri (merchantIdForIsolation perlu data driver-nya).
        if ($model instanceof \App\Models\Driver && $user->isDriver()) {
            return;
        }
        if ($user->isPlatformAdmin()) {
            return;
        }

        // Jangan batasi saat mengakses halaman publik marketplace
        if ($this->isPublicContext()) {
            return;
        }

        $merchantId = $user->merchantIdForIsolation();
        if ($merchantId === null) {
            return;
        }

        $builder->where($model->getTable() . '.' . $column, $merchantId);
    }

    protected function isPublicContext(): bool
    {
        $route = request()->route();
        if (!$route || !$route->getName()) {
            return false;
        }

        return in_array($route->getName(), $this->publicRoutes, true);
    }
}
