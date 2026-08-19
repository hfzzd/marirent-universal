<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('inspector_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['pre_rental', 'post_rental']);
            $table->integer('exterior_condition')->default(5);
            $table->integer('interior_condition')->default(5);
            $table->integer('engine_condition')->default(5);
            $table->integer('tire_condition')->default(5);
            $table->integer('brake_condition')->default(5);
            $table->integer('electrical_condition')->default(5);
            $table->integer('overall_condition')->default(5);
            $table->decimal('fuel_level', 5, 2)->default(100);
            $table->decimal('odometer_reading', 12, 2)->nullable();
            $table->json('damages')->nullable();
            $table->json('photos')->nullable();
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
