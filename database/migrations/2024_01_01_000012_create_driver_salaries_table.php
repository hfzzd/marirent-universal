<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('period_month', 7);
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('trip_bonus', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('total_salary', 12, 2);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_salaries');
    }
};
