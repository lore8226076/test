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
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 255)->comment('使用者ID');

            // 登入統計
            $table->integer('login_total_days')->default(0)->comment('累計登入天數');
            $table->integer('login_streak_days')->default(0)->comment('目前連續登入天數');
            $table->integer('login_streak_max')->default(0)->comment('最大連續登入天數');

            // 儲值統計
            $table->decimal('recharge_peak_amount', 10, 2)->default(0)->comment('最高單次儲值金額');
            $table->decimal('recharge_recent_amount', 10, 2)->default(0)->comment('最近新增儲值金額');
            $table->decimal('recharge_total_amount', 15, 2)->default(0)->comment('儲值總額');

            // 抽卡紀錄
            $table->integer('gacha_draw_times')->default(0)->comment('當前抽卡次數');
            $table->integer('gacha_draw_total')->default(0)->comment('累計抽卡次數');

            // 地圖編輯
            $table->integer('map_edit_times')->default(0)->comment('當前編輯地圖次數');
            $table->integer('map_edit_total')->default(0)->comment('累計編輯地圖次數');

            // 玩家關卡（UGC）
            $table->integer('ugc_play_times')->default(0)->comment('當前遊玩玩家關卡次數');
            $table->integer('ugc_play_total')->default(0)->comment('累計遊玩玩家關卡次數');
            $table->integer('ugc_clear_times')->default(0)->comment('當前通關玩家關卡次數');
            $table->integer('ugc_clear_total')->default(0)->comment('累計通關玩家關卡次數');

            // 紙娃娃配件
            $table->integer('sr_accessory_obtained_count')->default(0)->comment('取得 SR 配件數');
            $table->integer('sr_accessory_owned_count')->default(0)->comment('擁有 SR 配件數');
            $table->integer('ssr_accessory_obtained_count')->default(0)->comment('取得 SSR 配件數');
            $table->integer('ssr_accessory_owned_count')->default(0)->comment('擁有 SSR 配件數');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
