<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->enum('status', ['open', 'reported', 'processing', 'completed'])->default('open')->after('scope');
            $table->unsignedBigInteger('reported_by')->nullable()->after('inspector_id');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('reported_by');
            $table->text('resolution_notes')->nullable()->after('recommendations');

            $table->foreign('reported_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['reported_by']);
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['status', 'reported_by', 'assigned_to', 'resolution_notes']);
        });
    }
};