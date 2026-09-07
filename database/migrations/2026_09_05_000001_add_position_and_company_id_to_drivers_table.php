<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('owner_id')->constrained('companies')->nullOnDelete();
            $table->string('position')->nullable()->after('company_id');
        });

        \Illuminate\Support\Facades\DB::statement("
            UPDATE drivers d
            JOIN companies c ON c.user_id = d.owner_id
            SET d.company_id = c.id
        ");
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn('position');
        });
    }
};