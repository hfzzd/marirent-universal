<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_replacements', function (Blueprint $table) {
            $table->boolean('mark_maintenance')->default(true)->after('price_difference');
        });

        Schema::table('item_replacements', function (Blueprint $table) {
            $table->boolean('mark_maintenance')->default(false)->after('price_difference');
            $table->text('damage_notes')->nullable()->after('mark_maintenance');
            $table->text('return_notes')->nullable()->after('final_item_photo');
            $table->unsignedTinyInteger('return_condition')->nullable()->after('return_notes');
            $table->boolean('is_returned')->default(false)->after('return_condition');
            $table->timestamp('returned_at')->nullable()->after('is_returned');
            $table->foreignId('returned_by')->nullable()->after('returned_at')->constrained('users')->nullOnDelete();
            $table->boolean('return_is_damaged')->default(false)->after('returned_by');
            $table->text('return_damage_notes')->nullable()->after('return_is_damaged');
        });
    }

    public function down(): void
    {
        Schema::table('item_replacements', function (Blueprint $table) {
            $table->dropForeign(['returned_by']);
            $table->dropColumn([
                'mark_maintenance',
                'damage_notes',
                'return_notes',
                'return_condition',
                'is_returned',
                'returned_at',
                'returned_by',
                'return_is_damaged',
                'return_damage_notes',
            ]);
        });

        Schema::table('vehicle_replacements', function (Blueprint $table) {
            $table->dropColumn('mark_maintenance');
        });
    }
};
