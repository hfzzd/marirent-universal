<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->nullable()->after('total_amount');
            $table->decimal('platform_fee', 15, 2)->default(0)->after('commission_rate');
            $table->decimal('merchant_revenue', 15, 2)->default(0)->after('platform_fee');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['commission_rate', 'platform_fee', 'merchant_revenue']);
        });
    }
};
