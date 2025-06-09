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
        Schema::create('user_world', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->comment('ユーザーID');
            $table->foreignId('world_id')->constrained()->comment('ワールドID');
            $table->boolean('is_selected')->default(false)->comment('デフォルトでエントリーするワールドかどうか');
            $table->timestamps();
            $table->softDeletes();

            // ユーザーとワールドの組み合わせでユニーク
            $table->unique(['user_id', 'world_id'], 'user_world_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_world');
    }
};