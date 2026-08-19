<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('original_vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('replacement_vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('reason');
            $table->text('admin_notes')->nullable();
            $table->decimal('price_difference', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_replacements');
    }
};
