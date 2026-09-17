<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rekening bank tiap merchant untuk pencairan/komisi.
     */
    public function up(): void
    {
        foreach (['merchants', 'companies'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('bank_name', 80)->nullable()->after('operational_hours');
                $t->string('bank_account_number', 40)->nullable()->after('bank_name');
                $t->string('bank_account_holder', 120)->nullable()->after('bank_account_number');
            });
        }
    }

    public function down(): void
    {
        foreach (['merchants', 'companies'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['bank_name', 'bank_account_number', 'bank_account_holder']);
            });
        }
    }
};
