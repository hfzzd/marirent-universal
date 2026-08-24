<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // booking_id boleh null (invoice gabungan memakai tabel pivot)
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
        });
        DB::statement('ALTER TABLE invoices MODIFY booking_id BIGINT UNSIGNED NULL');
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
        });

        Schema::create('booking_invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['invoice_id', 'booking_id']);
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_invoice');

        DB::statement('UPDATE invoices SET booking_id = id WHERE booking_id IS NULL AND deleted_at IS NULL');
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
        });
        DB::statement('ALTER TABLE invoices MODIFY booking_id BIGINT UNSIGNED NOT NULL');
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
        });
    }
};
