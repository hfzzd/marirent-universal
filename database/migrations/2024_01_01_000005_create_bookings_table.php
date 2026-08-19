<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('item_type')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->enum('rental_type', ['hourly', 'daily', 'weekly', 'monthly']);
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->datetime('actual_start_date')->nullable();
            $table->datetime('actual_end_date')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->boolean('with_driver')->default(false);
            $table->decimal('base_price', 12, 2);
            $table->decimal('driver_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('final_price', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'ongoing', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
