<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('deposit_amount', 12, 2)->default(0)->after('driver_price');
            $table->decimal('insurance_fee', 12, 2)->default(0)->after('deposit_amount');
            $table->json('accessories')->nullable()->after('insurance_fee');
            $table->enum('urgency', ['normal', 'urgent', 'very_urgent'])->default('normal')->after('accessories');
            $table->boolean('with_insurance')->default(false)->after('urgency');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'insurance_fee', 'accessories', 'urgency', 'with_insurance']);
        });
    }
};
