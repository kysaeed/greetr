<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('world_entries', function (Blueprint $table) {
            $table->integer('day_of_week')->nullable()->comment('曜日（0:日曜, 1:月曜, ..., 6:土曜）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_entries', function (Blueprint $table) {
            $table->dropColumn('day_of_week');
        });
    }
};
