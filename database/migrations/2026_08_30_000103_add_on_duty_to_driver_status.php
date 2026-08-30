<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::statement("ALTER TABLE drivers MODIFY status ENUM('active', 'inactive', 'on_trip', 'off_duty', 'on_duty') NOT NULL DEFAULT 'off_duty'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE drivers MODIFY status ENUM('active', 'inactive', 'on_trip', 'off_duty') NOT NULL DEFAULT 'inactive'");
    }
};