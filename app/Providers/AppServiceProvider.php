<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Phone;
use App\Models\Camera;
use App\Models\CampingEquipment;
use App\Models\Playstation;
use App\Models\Drone;
use App\Models\MusicalInstrument;
use App\Observers\PaymentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);

        Relation::morphMap([
            'vehicle' => Vehicle::class,
            'phone' => Phone::class,
            'camera' => Camera::class,
            'camping' => CampingEquipment::class,
            'playstation' => Playstation::class,
            'drone' => Drone::class,
            'musical_instrument' => MusicalInstrument::class,
        ]);
    }
}
