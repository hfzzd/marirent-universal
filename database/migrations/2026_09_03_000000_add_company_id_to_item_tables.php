<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function itemTables(): array
    {
        return [
            'vehicles',
            'phones',
            'cameras',
            'camping_equipments',
            'playstations',
            'drones',
            'musical_instruments',
        ];
    }

    public function up(): void
    {
        foreach ($this->itemTables() as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('company_id')->nullable()->after('owner_id');
            });

            DB::statement("
                UPDATE {$table} i
                LEFT JOIN companies c ON c.user_id = i.owner_id
                SET i.company_id = c.id
                WHERE c.id IS NOT NULL
            ");

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->itemTables() as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['company_id']);
                $t->dropColumn('company_id');
            });
        }
    }
};
