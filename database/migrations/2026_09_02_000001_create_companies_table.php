<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('website')->nullable();
            $table->string('instagram')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('pickup_address')->nullable();
            $table->string('operational_hours')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Backfill: salin data perusahaan/toko yang sudah ada di tabel merchants
        // yang dimiliki akun owner (user_id = pemilik toko).
        DB::statement("
            INSERT INTO companies
                (user_id, slug, name, description, logo, banner, phone, company_email,
                 website, instagram, address, city, pickup_address, operational_hours,
                 commission_rate, is_active, status, verified_at,
                 deleted_at, created_at, updated_at)
            SELECT
                m.user_id, m.slug, m.name, m.description, m.logo, m.banner, m.phone, m.company_email,
                m.website, m.instagram, m.address, m.city, m.pickup_address, m.operational_hours,
                m.commission_rate, m.is_active, m.status, m.verified_at,
                m.deleted_at, m.created_at, m.updated_at
            FROM merchants m
            JOIN users u ON u.id = m.user_id
            WHERE u.role = 'owner'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};