<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah role inspector ke enum users.role
        \DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'owner', 'user', 'driver', 'inspector') NOT NULL DEFAULT 'user'");

        // Booking: jadwal pembayaran + sumber booking (online / manual)
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('payment_due_date')->nullable()->after('payment_status');
            $table->enum('source', ['online', 'manual'])->default('online')->after('payment_due_date');
        });

        // Inspeksi: dukungan item non-kendaraan, pilihan kerusakan, lama pemakaian
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
        });

        \DB::statement('ALTER TABLE inspections MODIFY vehicle_id BIGINT UNSIGNED NULL');

        Schema::table('inspections', function (Blueprint $table) {
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->string('item_type')->nullable()->after('vehicle_id');
            $table->unsignedBigInteger('item_id')->nullable()->after('item_type');
            $table->enum('scope', ['kendaraan', 'elektronik', 'camping'])->default('kendaraan')->after('type');
            $table->integer('usage_duration_hours')->nullable()->after('odometer_reading');
            $table->json('damage_items')->nullable()->after('damages');
            $table->json('completeness')->nullable()->after('damage_items');
            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'owner', 'user', 'driver') NOT NULL DEFAULT 'user'");

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_due_date', 'source']);
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropIndex(['item_type', 'item_id']);
            $table->dropColumn(['item_type', 'item_id', 'scope', 'usage_duration_hours', 'damage_items', 'completeness']);
        });

        \DB::statement('ALTER TABLE inspections MODIFY vehicle_id BIGINT UNSIGNED NOT NULL');

        Schema::table('inspections', function (Blueprint $table) {
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });
    }
};
