<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->decimal('start_odometer', 12, 2)->nullable();
            $table->decimal('end_odometer', 12, 2)->nullable();
            $table->decimal('total_distance', 10, 2)->nullable();
            $table->decimal('fuel_used', 5, 2)->nullable();
            $table->decimal('fuel_cost', 12, 2)->default(0);
            $table->decimal('toll_cost', 12, 2)->default(0);
            $table->decimal('parking_cost', 12, 2)->default(0);
            $table->decimal('other_cost', 12, 2)->default(0);
            $table->decimal('total_operational_cost', 12, 2)->default(0);
            $table->json('route_points')->nullable();
            $table->json('photos')->nullable();
            $table->text('notes')->nullable();
            $table->text('issues_reported')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'has_issues'])->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_reports');
    }
};
