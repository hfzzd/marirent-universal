<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->enum('billing_plan', ['commission', 'subscription'])
                ->default('commission')
                ->after('commission_rate');
            $table->decimal('subscription_fee', 14, 2)
                ->default(500000)
                ->after('billing_plan');
            $table->timestamp('subscription_until')
                ->nullable()
                ->after('subscription_fee');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['billing_plan', 'subscription_fee', 'subscription_until']);
        });
    }
};