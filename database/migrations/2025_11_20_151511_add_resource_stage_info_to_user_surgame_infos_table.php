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
        Schema::table('user_surgame_infos', function (Blueprint $table) {
            // 掃蕩相關
            $table->integer('money_sweep_free_total')->default(2)->comment('金幣每天可免費掃蕩次數(固定2)');
            $table->integer('money_sweep_free_left')->default(2)->comment('金幣今天剩餘免費掃蕩次數');
            $table->integer('money_sweep_pay_total')->default(0)->comment('金幣今天可付費掃蕩總次數');
            $table->integer('money_sweep_pay_left')->default(0)->comment('金幣今天剩餘可付費掃蕩次數');
            $table->integer('exp_sweep_free_total')->default(2)->comment('經驗每天可免費掃蕩次數(固定2)');
            $table->integer('exp_sweep_free_left')->default(2)->comment('經驗今天剩餘免費掃蕩次數');
            $table->integer('exp_sweep_pay_total')->default(0)->comment('經驗今天可付費掃蕩總次數');
            $table->integer('exp_sweep_pay_left')->default(0)->comment('經驗今天剩餘可付費掃蕩次數');
            $table->integer('gift_sweep_free_total')->default(2)->comment('裝備每天可免費掃蕩次數(固定2)');
            $table->integer('gift_sweep_free_left')->default(2)->comment('裝備今天剩餘免費掃蕩次數');
            $table->integer('gift_sweep_pay_total')->default(0)->comment('裝備今天可付費掃蕩總次數');
            $table->integer('gift_sweep_pay_left')->default(0)->comment('裝備今天剩餘可付費掃蕩次數');
            $table->integer('sweep_pay_item_id')->default(100)->comment('付費掃蕩使用的貨幣ID');
            $table->integer('sweep_pay_amount')->default(20)->comment('每次付費掃蕩要花多少貨幣');

            // 獎勵加成
            $table->integer('resource_stage_bonus_rate')->default(0)->comment('獎勵加成比例(%)');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_surgame_infos', function (Blueprint $table) {
            $table->dropColumn([
                'sweep_free_total',
                'sweep_free_left',
                'sweep_pay_total',
                'sweep_pay_left',
                'sweep_pay_item_id',
                'sweep_pay_amount',
                'resource_stage_bonus_rate',
                'current_stage_id',
            ]);
        });
    }
};
