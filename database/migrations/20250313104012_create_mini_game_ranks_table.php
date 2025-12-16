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
        Schema::create('mini_game_ranks', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id')->comment('遊戲ID');
            $table->integer('user_id')->comment('使用者ID');
            $table->integer('score')->comment('分數');
            $table->integer('total_time')->comment('總花費時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mini_game_ranks');
    }
};
