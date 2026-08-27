<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_catalog_photos', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->enum('item_type', ['mobil', 'motor', 'hp', 'kamera', 'camping']);
            $table->string('photo_path');
            $table->text('caption')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_catalog_photos');
    }
};
