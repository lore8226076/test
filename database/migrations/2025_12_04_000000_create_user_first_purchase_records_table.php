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
        Schema::create('user_first_purchase_records', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用戶 ID');
            $table->unsignedBigInteger('uid')->index()->comment('玩家 UID');
            $table->string('product_id', 50)->comment('商品 ID (gp0001, gp0007...)');
            $table->string('purchase_type', 20)->comment('購買類型: item(道具), month_card(月卡)');
            $table->unsignedBigInteger('item_id')->nullable()->comment('道具 ID（道具類型時使用）');
            $table->unsignedBigInteger('month_card_config_id')->nullable()->comment('月卡配置 ID（月卡類型時使用）');
            $table->boolean('is_first_purchase')->default(true)->comment('是否為首次購買');
            $table->boolean('reward_sent')->default(false)->comment('是否已發放首購獎勵');
            $table->dateTime('first_purchase_at')->comment('首次購買時間');
            $table->dateTime('reward_sent_at')->nullable()->comment('獎勵發放時間');
            $table->text('reward_detail')->nullable()->comment('獎勵詳情 JSON');
            $table->timestamps();

            // 索引
            $table->index('user_id');
            $table->index(['uid', 'product_id']);
            $table->index(['uid', 'purchase_type']);
            $table->unique(['user_id', 'product_id'], 'user_product_unique');

            // 外鍵
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_first_purchase_records');
    }
};
