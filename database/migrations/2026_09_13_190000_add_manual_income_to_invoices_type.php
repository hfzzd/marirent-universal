<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('type', ['rental', 'driver_salary', 'replacement', 'damage', 'manual_income', 'other'])
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('type', ['rental', 'driver_salary', 'replacement', 'damage', 'other'])
                ->change();
        });
    }
};