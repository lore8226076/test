<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_stats', function (Blueprint $table) {
            // 商城幣與遊戲幣
            $table->integer('mission_spend_mall_coin')->default(0)->comment('任務期間花費商城幣');
            $table->integer('spend_mall_coin_total')->default(0)->comment('累計花費商城幣');
            $table->integer('mission_spend_game_coin')->default(0)->comment('任務期間花費遊戲幣');
            $table->integer('spend_game_coin_total')->default(0)->comment('累計花費遊戲幣');

            // 地圖發布
            $table->integer('map_publish_total')->default(0)->comment('累計地圖發布次數');

            // 家園拜訪
            $table->integer('mission_visit_home')->default(0)->comment('任務期間拜訪好友家園次數');
            $table->integer('visit_home_total')->default(0)->comment('累計拜訪好友家園次數');

            // 紙娃娃配件總數
            $table->integer('doll_accessory_obtained_total')->default(0)->comment('取得紙娃娃配件總數');

            // 家具類型
            $table->integer('furniture_type_obtained_total')->default(0)->comment('取得家具類型總數');
            $table->integer('furniture_type_owned_total')->default(0)->comment('擁有家具類型總數');

            // 小遊戲
            $table->integer('minigame_play_total')->default(0)->comment('小遊戲遊玩次數');

            // 寵物等級
            $table->integer('pet_level_total')->default(0)->comment('寵物總等級');
            for ($i = 1; $i <= 6; $i++) {
                $table->integer("pet_{$i}_level")->default(0)->comment("寵物{$i}等級");
            }

            // 召喚次數統計
            $table->integer('summon_1_or_more_pets_count')->default(0)->comment('召喚 1 隻以上寵物次數');
            $table->integer('summon_2_or_more_pets_count')->default(0)->comment('召喚 2 隻以上寵物次數');
            $table->integer('summon_3_or_more_pets_count')->default(0)->comment('召喚 3 隻以上寵物次數');
            for ($i = 1; $i <= 6; $i++) {
                $table->integer("summon_pet_{$i}_total_count")->default(0)->comment("召喚寵物{$i}總次數");
            }
            $table->integer('summon_same_pet_3_or_more_times')->default(0)->comment('召喚同隻寵物達 3 次以上次數');

            // 成就類統計
            $table->integer('max_followers_count')->default(0)->comment('最大粉絲數');
            $table->integer('max_map_like_count')->default(0)->comment('最大地圖按讚數');

            // 廣告
            $table->integer('mission_watch_ads_count')->default(0)->comment('任務期間觀看廣告次數');
        });
    }

    public function down(): void
    {
        Schema::table('user_stats', function (Blueprint $table) {
            $table->dropColumn('mission_spend_mall_coin');
            $table->dropColumn('spend_mall_coin_total');
            $table->dropColumn('mission_spend_game_coin');
            $table->dropColumn('spend_game_coin_total');
            $table->dropColumn('map_publish_total');
            $table->dropColumn('mission_visit_home');
            $table->dropColumn('visit_home_total');
            $table->dropColumn('doll_accessory_obtained_total');
            $table->dropColumn('furniture_type_obtained_total');
            $table->dropColumn('furniture_type_owned_total');
            $table->dropColumn('minigame_play_total');
            $table->dropColumn('pet_level_total');
            $table->dropColumn('pet_1_level');
            $table->dropColumn('pet_2_level');
            $table->dropColumn('pet_3_level');
            $table->dropColumn('pet_4_level');
            $table->dropColumn('pet_5_level');
            $table->dropColumn('pet_6_level');
            $table->dropColumn('summon_1_or_more_pets_count');
            $table->dropColumn('summon_2_or_more_pets_count');
            $table->dropColumn('summon_3_or_more_pets_count');
            $table->dropColumn('summon_pet_1_total_count');
            $table->dropColumn('summon_pet_2_total_count');
            $table->dropColumn('summon_pet_3_total_count');
            $table->dropColumn('summon_pet_4_total_count');
            $table->dropColumn('summon_pet_5_total_count');
            $table->dropColumn('summon_pet_6_total_count');
            $table->dropColumn('summon_same_pet_3_or_more_times');
            $table->dropColumn('max_followers_count');
            $table->dropColumn('max_map_like_count');
            $table->dropColumn('mission_watch_ads_count');
        });
    }
};
