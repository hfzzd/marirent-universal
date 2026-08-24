<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Alias morph legacy: baris lama menyimpan item_type = 'vehicle'
        Relation::morphMap([
            'vehicle' => \App\Models\Vehicle::class,
        ]);
    }
}
