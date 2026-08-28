<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_replacements', function (Blueprint $table) {
            $table->enum('item_type', ['hp', 'camera', 'tenda', 'ps', 'drone', 'musik'])->change();
        });

        Schema::table('brand_catalog_photos', function (Blueprint $table) {
            $table->enum('item_type', ['mobil', 'motor', 'hp', 'kamera', 'camping', 'ps', 'drone', 'musik'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('item_replacements', function (Blueprint $table) {
            $table->enum('item_type', ['hp', 'camera', 'tenda'])->change();
        });

        Schema::table('brand_catalog_photos', function (Blueprint $table) {
            $table->enum('item_type', ['mobil', 'motor', 'hp', 'kamera', 'camping'])->change();
        });
    }
};
