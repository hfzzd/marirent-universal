<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->enum('item_type', ['hp', 'camera', 'tenda']);
            $table->unsignedBigInteger('original_item_id');
            $table->unsignedBigInteger('replacement_item_id');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reason');
            $table->text('admin_notes')->nullable();
            $table->decimal('price_difference', 12, 2)->default(0);
            $table->string('initial_item_photo')->nullable();
            $table->string('final_item_photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_replacements');
    }
};
