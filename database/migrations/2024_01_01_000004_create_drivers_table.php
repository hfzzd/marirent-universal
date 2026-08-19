<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('license_type')->nullable();
            $table->decimal('daily_salary', 12, 2)->default(0);
            $table->decimal('trip_salary', 12, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'on_trip', 'off_duty'])->default('off_duty');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
