<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah role inspector ke enum users.role
        DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'owner', 'user', 'driver', 'inspector') NOT NULL DEFAULT 'user'");

        // Booking: jadwal pembayaran + sumber booking (online / manual)
        // Guard: skip jika kolom sudah ada
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'payment_due_date')) {
                $table->date('payment_due_date')->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('bookings', 'source')) {
                $table->enum('source', ['online', 'manual'])->default('online')->after('payment_due_date');
            }
        });

        // Inspeksi: dukungan item non-kendaraan, pilihan kerusakan, lama pemakaian
        // Catatan: vehicle_id sudah tidak ada di tabel inspections (sudah dihapus sebelumnya)
        // Hanya tambahkan kolom yang belum ada
        Schema::table('inspections', function (Blueprint $table) {
            if (! Schema::hasColumn('inspections', 'scope')) {
                $table->enum('scope', ['kendaraan', 'elektronik', 'camping'])->default('kendaraan')->after('type');
            }
            if (! Schema::hasColumn('inspections', 'usage_duration_hours')) {
                $table->integer('usage_duration_hours')->nullable()->after('odometer_reading');
            }
            if (! Schema::hasColumn('inspections', 'damage_items')) {
                $table->json('damage_items')->nullable()->after('damages');
            }
            if (! Schema::hasColumn('inspections', 'completeness')) {
                $table->json('completeness')->nullable()->after('damage_items');
            }

            // Guard: tambah index hanya jika belum ada
            try {
                $table->index(['item_type', 'item_id']);
            } catch (Throwable $e) {
                // Index sudah ada, lanjutkan
            }
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'owner', 'user', 'driver') NOT NULL DEFAULT 'user'");

        Schema::table('bookings', function (Blueprint $table) {
            $toDrop = array_filter(
                ['payment_due_date', 'source'],
                fn ($col) => Schema::hasColumn('bookings', $col)
            );
            if ($toDrop) {
                $table->dropColumn(array_values($toDrop));
            }
        });

        Schema::table('inspections', function (Blueprint $table) {
            try {
                $table->dropIndex(['item_type', 'item_id']);
            } catch (Throwable $e) {
            }

            $toDrop = array_filter(
                ['scope', 'usage_duration_hours', 'damage_items', 'completeness'],
                fn ($col) => Schema::hasColumn('inspections', $col)
            );
            if ($toDrop) {
                $table->dropColumn(array_values($toDrop));
            }
        });
    }
};
