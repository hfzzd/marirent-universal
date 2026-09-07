<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'users',
            'merchants',
            'companies',
            'vehicles',
            'phones',
            'cameras',
            'camping_equipments',
            'playstations',
            'drones',
            'musical_instruments',
        ] as $table) {
            DB::statement("\n                UPDATE {$table}\n                SET name = TRIM(LEFT(RTRIM(name), CHAR_LENGTH(RTRIM(name)) - 8))\n                WHERE name IS NOT NULL\n                  AND CHAR_LENGTH(RTRIM(name)) > 8\n                  AND LOWER(RIGHT(RTRIM(name), 8)) = '(driver)'\n            ");
        }
    }

    public function down(): void
    {
        // Suffix cleanup is intentionally not reversed because the original value is unknown.
    }
};
