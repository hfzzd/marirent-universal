<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_replacements', function (Blueprint $table) {
            $table->enum('handover_type', ['lepas_kunci', 'with_driver'])->default('lepas_kunci')->after('price_difference');
            $table->text('handover_notes')->nullable()->after('handover_type');
            $table->timestamp('actual_handover_at')->nullable()->after('handover_notes');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_replacements', function (Blueprint $table) {
            $table->dropColumn(['handover_type', 'handover_notes', 'actual_handover_at']);
        });
    }
};
