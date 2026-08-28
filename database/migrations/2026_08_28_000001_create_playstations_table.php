<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playstations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand')->nullable();
            $table->string('console_model')->nullable();
            $table->string('storage_capacity')->nullable();
            $table->integer('controllers_count')->default(2);
            $table->string('color')->nullable();
            $table->json('accessories')->nullable();
            $table->text('description')->nullable();
            $table->decimal('daily_price', 12, 2);
            $table->decimal('weekly_price', 12, 2)->nullable();
            $table->decimal('monthly_price', 12, 2)->nullable();
            $table->decimal('hourly_price', 12, 2)->nullable();
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->enum('status', ['available', 'rented', 'maintenance', 'reserved'])->default('available');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playstations');
    }
};
