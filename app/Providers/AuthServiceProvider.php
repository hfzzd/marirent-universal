<?php

namespace App\Providers;

use App\Models\Rental;
use App\Models\User;
use App\Policies\RentalPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Rental::class => RentalPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
