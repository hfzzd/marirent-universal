<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_replacements', function (Blueprint $table) {
            $table->string('initial_vehicle_photo')->nullable()->after('actual_handover_at');
            $table->string('final_vehicle_photo')->nullable()->after('initial_vehicle_photo');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_replacements', function (Blueprint $table) {
            $table->dropColumn(['initial_vehicle_photo', 'final_vehicle_photo']);
        });
    }
};
