<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('item_type', ['vehicle','phone','camera','camping'])->nullable()->default(null)->change();
            $table->unsignedBigInteger('item_id')->nullable()->change();
            $table->foreignId('category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('item_type', ['vehicle','phone','camera','camping'])->nullable(false)->default('vehicle')->change();
            $table->unsignedBigInteger('item_id')->nullable(false)->change();
            $table->foreignId('category_id')->nullable(false)->change();
        });
    }
};
