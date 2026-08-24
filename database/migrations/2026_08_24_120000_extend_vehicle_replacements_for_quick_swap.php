<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_replacements', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->change();
            $table->foreignId('rental_id')->nullable()->after('booking_id')->constrained('rentals')->nullOnDelete();
            $table->timestamp('swapped_at')->nullable()->after('status');
            $table->index('rental_id');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_replacements', function (Blueprint $table) {
            $table->dropIndex(['rental_id']);
            $table->dropConstrainedForeignId('rental_id');
            $table->dropColumn('swapped_at');
            $table->foreignId('booking_id')->nullable(false)->change();
        });
    }
};
