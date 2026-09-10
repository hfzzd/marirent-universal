<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'admin', 'owner', 'user', 'driver', 'employee', 'inspector') NOT NULL DEFAULT 'user'");

        DB::statement("\n            UPDATE users u\n            JOIN drivers d ON d.user_id = u.id\n            SET u.role = 'employee'\n            WHERE u.role = 'driver'\n              AND d.position IS NOT NULL\n              AND (\n                  LOWER(TRIM(d.position)) LIKE '%non driver%'\n                  OR LOWER(TRIM(d.position)) LIKE '%non-driver%'\n                  OR LOWER(TRIM(d.position)) LIKE '%bukan driver%'\n                  OR (\n                      LOWER(TRIM(d.position)) NOT LIKE '%driver%'\n                      AND LOWER(TRIM(d.position)) NOT LIKE '%supir%'\n                      AND LOWER(TRIM(d.position)) NOT LIKE '%pengemudi%'\n                  )\n              )\n        ");

        DB::statement("\n            UPDATE users u\n            JOIN categories c ON c.id = u.category_id\n            SET u.role = 'employee'\n            WHERE u.role = 'driver'\n              AND LOWER(TRIM(c.slug)) NOT IN ('mobil', 'motor')\n        ");
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'employee')->update(['role' => 'driver']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'admin', 'owner', 'user', 'driver', 'inspector') NOT NULL DEFAULT 'user'");
    }
};
