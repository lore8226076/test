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
        Schema::table('material_stages', function (Blueprint $table) {

            $table->dropColumn('reward_items');
            $table->dropColumn('reward_items_rate');
            $table->string('image_path')->nullable()->comment('圖片路徑');                // 圖片路徑
            $table->json('random_reward_items_rate')->nullable()->comment('隨機獎勵物品機率');      // 隨機獎勵物品機率
            $table->integer('random_reward_count')->default(0)->comment('隨機獎勵次數');      // 隨機獎勵次數
            $table->json('random_reward')->nullable()->comment('隨機獎勵');               // 隨機獎勵
            $table->json('fixed_reward')->nullable()->comment('固定獎勵');                // 固定獎勵
            $table->integer('player_level')->default(0)->comment('玩家等級');             // 玩家等級
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_stages', function (Blueprint $table) {
            $table->dropColumn('image_path');
            $table->dropColumn('random_reward_items_rate');
            $table->dropColumn('random_reward_count');
            $table->dropColumn('random_reward');
            $table->dropColumn('fixed_reward');
            $table->dropColumn('player_level');
            $table->json('reward_items')->nullable()->comment('獎勵物品'); // 獎勵物品
            $table->json('reward_items_rate')->nullable()->comment('獎勵物品機率'); // 獎勵物品機率
        });
    }
};
