<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('item_type', 255)->nullable()->change();
            $table->enum('payment_plan', ['full', 'dp50'])->default('full')->after('with_insurance');
        });

        $map = [
            'vehicle' => 'App\Models\Vehicle',
            'phone' => 'App\Models\Phone',
            'camera' => 'App\Models\Camera',
            'camping' => 'App\Models\CampingEquipment',
        ];
        foreach ($map as $short => $full) {
            DB::table('bookings')->where('item_type', $short)->update(['item_type' => $full]);
        }
    }

    public function down(): void
    {
        $map = [
            'App\Models\Vehicle' => 'vehicle',
            'App\Models\Phone' => 'phone',
            'App\Models\Camera' => 'camera',
            'App\Models\CampingEquipment' => 'camping',
        ];
        foreach ($map as $full => $short) {
            DB::table('bookings')->where('item_type', $full)->update(['item_type' => $short]);
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_plan');
            $table->enum('item_type', ['vehicle', 'phone', 'camera', 'camping'])->nullable()->change();
        });
    }
};