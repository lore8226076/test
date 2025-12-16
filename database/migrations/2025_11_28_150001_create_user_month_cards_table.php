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
        Schema::create('user_month_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('uid')->index()->comment('玩家 UID');
            $table->dateTime('purchased_at')->comment('購買/延長時間');
            $table->dateTime('expire_at')->nullable()->comment('到期時間，永久卡為 NULL');
            $table->date('last_daily_reward_at')->nullable()->comment('上次領取每日獎勵的日期');
            $table->unsignedInteger('total_purchase_times')->default(1)->comment('累計購買次數');
            $table->timestamps();

            $table->foreignId('month_card_config_id')->constrained('month_card_configs')->cascadeOnDelete();
            // 每個用戶每種月卡只能有一筆記錄
            $table->unique(['user_id', 'month_card_config_id'], 'user_month_card_unique');
            $table->index('expire_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_month_cards');
    }
};
