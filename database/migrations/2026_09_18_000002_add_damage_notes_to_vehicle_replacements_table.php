<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Samakan formulir penggantian kendaraan dengan penggantian unit:
     * unit asal bisa dicatat detail kerusakannya.
     */
    public function up(): void
    {
        Schema::table('vehicle_replacements', function (Blueprint $t) {
            $t->text('damage_notes')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_replacements', function (Blueprint $t) {
            $t->dropColumn('damage_notes');
        });
    }
};
