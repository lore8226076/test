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
        Schema::create('month_card_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->nullable()->index()->comment('商城商品 ID');
            $table->string('localization_name')->comment('顯示名稱/本地化 key');
            $table->json('basic_reward')->nullable()->comment('首次購買獎勵');
            $table->json('daily_reward')->nullable()->comment('每日可領取獎勵');
            $table->integer('add_days')->nullable()->comment('購買後延長天數，永久卡可為 NULL');
            $table->boolean('display_remaining')->default(true)->comment('是否顯示剩餘天數');
            $table->unsignedSmallInteger('max_purchase_times')->default(1)->comment('最大購買次數');
            $table->boolean('reset_buy_count')->default(false)->comment('購買次數是否重置');
            $table->boolean('enable_3x_speed')->default(false)->comment('是否解鎖 3 倍速');
            $table->boolean('unlimited_quick_patrol')->default(false)->comment('是否無限快速巡邏');
            $table->boolean('enable_patrol_reward')->default(false)->comment('是否啟用巡邏獎勵加成');
            $table->unsignedSmallInteger('patrol_reward_percent')->default(0)->comment('巡邏收益加成百分比');
            $table->unsignedSmallInteger('stage_reward_percent')->default(0)->comment('關卡獎勵加成百分比');
            $table->unsignedInteger('stamina_max')->default(0)->comment('額外體力上限');
            $table->unsignedSmallInteger('black_market_extra_refresh_times')->default(0)->comment('黑市額外刷新次數');
            $table->boolean('is_permanent')->default(false)->comment('是否為永久卡');
            $table->boolean('is_active')->default(true)->comment('是否啟用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('month_card_configs');
    }
};
