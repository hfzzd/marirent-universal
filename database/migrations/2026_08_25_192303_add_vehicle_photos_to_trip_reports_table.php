<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_reports', function (Blueprint $table) {
            $table->string('photo_front')->nullable()->after('photos');
            $table->string('photo_rear')->nullable()->after('photo_front');
            $table->string('photo_right')->nullable()->after('photo_rear');
            $table->string('photo_left')->nullable()->after('photo_right');
        });
    }

    public function down(): void
    {
        Schema::table('trip_reports', function (Blueprint $table) {
            $table->dropColumn(['photo_front', 'photo_rear', 'photo_right', 'photo_left']);
        });
    }
};
