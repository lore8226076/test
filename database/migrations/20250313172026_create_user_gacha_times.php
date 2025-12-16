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
        Schema::create('user_gacha_times', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index(); // 玩家ID
            $table->integer('uid')->index(); // 遊戲內唯一玩家ID
            $table->integer('gacha_id'); // 扭蛋池ID
            $table->integer('times'); // 抽取次數
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_gacha_times');
    }
};
