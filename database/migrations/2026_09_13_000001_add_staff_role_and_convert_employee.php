<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menjadikan `staff` sebagai role kanonis untuk personel non-sopir.
     * - Menambahkan `staff` ke enum (mempertahankan `employee` sebagai alias lawas).
     * - Mengonversi user `employee` yang ada menjadi `staff`.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'admin', 'owner', 'user', 'driver', 'staff', 'employee', 'inspector') NOT NULL DEFAULT 'user'");

        DB::table('users')->where('role', 'employee')->update(['role' => 'staff']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'staff')->update(['role' => 'employee']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'admin', 'owner', 'user', 'driver', 'employee', 'inspector') NOT NULL DEFAULT 'user'");
    }
};
