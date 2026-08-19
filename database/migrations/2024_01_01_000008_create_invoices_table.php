<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['rental', 'driver_salary', 'replacement', 'damage', 'other']);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('due_amount', 14, 2);
            $table->enum('status', ['draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled'])->default('draft');
            $table->enum('payment_method', ['cash', 'transfer', 'ewallet', 'credit_card', 'other'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->datetime('due_date');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
