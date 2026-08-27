<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'item_type')) {
                $table->string('item_type')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('reviews', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->after('item_type');
            }
        });

        // Migrate existing vehicle_id data to polymorphic columns
        if (Schema::hasColumn('reviews', 'vehicle_id')) {
            DB::table('reviews')
                ->whereNotNull('vehicle_id')
                ->update([
                    'item_type' => 'App\Models\Vehicle',
                    'item_id'   => DB::raw('vehicle_id'),
                ]);
        }

        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'vehicle_id')) {
                $table->dropForeign(['vehicle_id']);
                $table->dropColumn('vehicle_id');
            }
            if (!Schema::hasIndex('reviews', ['item_type', 'item_id'])) {
                $table->index(['item_type', 'item_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'vehicle_id')) {
                $table->unsignedBigInteger('vehicle_id')->nullable()->after('user_id');
            }
        });

        if (Schema::hasColumn('reviews', 'item_type')) {
            DB::table('reviews')
                ->where('item_type', 'App\Models\Vehicle')
                ->update(['vehicle_id' => DB::raw('item_id')]);
        }

        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasIndex('reviews', ['item_type', 'item_id'])) {
                $table->dropIndex(['item_type', 'item_id']);
            }
            if (Schema::hasColumn('reviews', 'item_type')) {
                $table->dropColumn(['item_type', 'item_id']);
            }
            if (!Schema::hasIndex('reviews', ['vehicle_id'])) {
                $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            }
        });
    }
};
