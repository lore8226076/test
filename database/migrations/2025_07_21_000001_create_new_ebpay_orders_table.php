<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_ebpay_orders', function (Blueprint $table) {
            $table->bigIncrements('id'); // 主鍵
            $table->string('order_no')->unique(); // 藍新訂單編號（唯一）
            $table->unsignedBigInteger('user_id')->nullable(); // 會員ID（可選）
            $table->integer('amount'); // 訂單金額
            $table->string('item_desc'); // 商品說明
            $table->string('email'); // 購買人Email
            $table->string('status')->default('pending'); // 狀態（pending/paid/failed/cancelled）
            $table->timestamp('paid_at')->nullable(); // 付款完成時間
            $table->timestamps(); // 建立時間、更新時間
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('new_ebpay_orders');
    }
};
