<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->string('company_email')->nullable()->after('phone');
            $table->string('website')->nullable()->after('company_email');
            $table->string('instagram')->nullable()->after('website');
            $table->timestamp('verified_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['company_email', 'website', 'instagram', 'verified_at']);
        });
    }
};
