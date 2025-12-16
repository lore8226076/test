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
        if (Schema::hasTable('gddb_surgame_heroes')) {
            Schema::dropIfExists('gddb_surgame_heroes');
        }
        Schema::create('gddb_surgame_heroes', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique()->comment('識別ID');
            $table->string('name', 64)->comment('名稱');
            $table->string('icon', 128)->nullable()->comment('圖示');
            $table->string('card', 64)->nullable()->comment('卡片代號');
            $table->string('prefab', 128)->nullable()->comment('Prefab 名稱');
            $table->string('skill_01', 64)->nullable()->comment('技能1');
            $table->string('skill_02', 64)->nullable()->comment('技能2');
            $table->string('skill_02_evo', 64)->nullable()->comment('技能2進化');
            $table->string('rarity', 32)->nullable()->comment('稀有度');
            $table->string('style_group', 64)->nullable()->comment('風格群組');
            $table->integer('convert_item_id')->default(-1)->comment('重複抽獎轉換的道具item_id');
            $table->unsignedBigInteger('rank_up_group')->nullable()->comment('升階群組');
            $table->unsignedBigInteger('rank_func_group')->nullable()->comment('Rank功能群組');
            $table->unsignedBigInteger('level_group')->nullable()->comment('等級群組');
            $table->integer('element')->default(0)->comment('屬性');
            $table->string('chain_skill', 64)->nullable()->comment('連鎖技能');
            $table->string('icon_main_skill', 128)->nullable()->comment('主動技能圖示');
            $table->string('icon_talent', 128)->nullable()->comment('天賦圖示');
            $table->string('icon_passive', 128)->nullable()->comment('被動圖示');

            $table->comment('角色英雄');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_heroes');
    }
};
