<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'admin', 'owner', 'user', 'driver', 'inspector') NOT NULL DEFAULT 'user'");

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable()->after('role');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');
        });

        \DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin', 'owner', 'user', 'driver', 'inspector') NOT NULL DEFAULT 'user'");
    }
};