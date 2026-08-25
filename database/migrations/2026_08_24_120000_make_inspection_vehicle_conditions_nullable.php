<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom kondisi khusus kendaraan boleh kosong utk scope elektronik/camping
        \DB::statement("ALTER TABLE inspections
            MODIFY exterior_condition TINYINT NULL DEFAULT NULL,
            MODIFY interior_condition TINYINT NULL DEFAULT NULL,
            MODIFY engine_condition TINYINT NULL DEFAULT NULL,
            MODIFY tire_condition TINYINT NULL DEFAULT NULL,
            MODIFY brake_condition TINYINT NULL DEFAULT NULL,
            MODIFY electrical_condition TINYINT NULL DEFAULT NULL,
            MODIFY fuel_level DECIMAL(5,2) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        \DB::statement("UPDATE inspections SET exterior_condition = COALESCE(exterior_condition, 5), interior_condition = COALESCE(interior_condition, 5), engine_condition = COALESCE(engine_condition, 5), tire_condition = COALESCE(tire_condition, 5), brake_condition = COALESCE(brake_condition, 5), electrical_condition = COALESCE(electrical_condition, 5)");

        \DB::statement("ALTER TABLE inspections
            MODIFY exterior_condition TINYINT NOT NULL DEFAULT 5,
            MODIFY interior_condition TINYINT NOT NULL DEFAULT 5,
            MODIFY engine_condition TINYINT NOT NULL DEFAULT 5,
            MODIFY tire_condition TINYINT NOT NULL DEFAULT 5,
            MODIFY brake_condition TINYINT NOT NULL DEFAULT 5,
            MODIFY electrical_condition TINYINT NOT NULL DEFAULT 5");
    }
};
