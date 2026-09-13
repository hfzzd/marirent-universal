<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $map = [
        'vehicle' => 'App\Models\Vehicle',
        'phone' => 'App\Models\Phone',
        'camera' => 'App\Models\Camera',
        'camping' => 'App\Models\CampingEquipment',
        'playstation' => 'App\Models\Playstation',
        'drone' => 'App\Models\Drone',
        'musical_instrument' => 'App\Models\MusicalInstrument',
    ];

    public function up(): void
    {
        foreach (['bookings', 'booking_items', 'reviews'] as $table) {
            foreach ($this->map as $short => $full) {
                DB::table($table)->where('item_type', $short)->update(['item_type' => $full]);
            }
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'booking_items', 'reviews'] as $table) {
            foreach ($this->map as $short => $full) {
                DB::table($table)->where('item_type', $full)->update(['item_type' => $short]);
            }
        }
    }
};