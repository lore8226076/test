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
        Schema::create('user_journey_star_challenges', function (Blueprint $table) {
            $table->id();
            $table->integer('uid');                     // 玩家 ID
            $table->unsignedBigInteger('challenge_id'); // 關卡 ID
            $table->unsignedTinyInteger('stars_mask')   // 用位元紀錄已完成的星星 (0b000 ~ 0b111)
                ->default(0);
            $table->timestamps();

            $table->unique(['uid', 'challenge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_journey_star_challenges');
    }
};
