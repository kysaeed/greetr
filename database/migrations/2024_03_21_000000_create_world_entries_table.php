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
        Schema::create('world_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->comment('ユーザーID');
            $table->foreignId('world_id')->constrained()->comment('ワールドID');
            $table->timestamp('entry_time')->comment('入場時刻');
            $table->integer('coins_earned')->default(0)->comment('獲得コイン数');
            $table->integer('day_of_week')->comment('曜日（0:日曜日 〜 6:土曜日）');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_entries');
    }
};