<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Koordinat lokasi (latitude/longitude) untuk merchant & company,
 * dipakai untuk menampilkan peta Google Maps Embed dan panel pin owner.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['merchants', 'companies'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->decimal('latitude', 10, 7)->nullable()->after('operational_hours');
                $t->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }
    }

    public function down(): void
    {
        foreach (['merchants', 'companies'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['latitude', 'longitude']);
            });
        }
    }
};