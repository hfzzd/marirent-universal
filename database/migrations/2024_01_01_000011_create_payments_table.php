<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_code')->unique();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 14, 2);
            $table->enum('method', ['cash', 'transfer', 'ewallet', 'credit_card', 'other']);
            $table->string('reference_number')->nullable();
            $table->string('proof_photo')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
