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
        Schema::create('new_ebpay_payments', function (Blueprint $table) {
            $table->bigIncrements('id'); // 主鍵
            $table->unsignedBigInteger('new_ebpay_order_id'); // 關聯 newebpay_orders.id
            $table->string('method'); // 付款方式（atm/credit）
            $table->integer('amount'); // 付款金額
            $table->string('status')->default('pending'); // 狀態（pending/success/fail）
            $table->string('trade_no')->nullable(); // 藍新金流交易編號
            $table->string('merchant_order_no')->nullable(); // 藍新商店訂單編號
            $table->string('bank_code')->nullable(); // ATM銀行代碼（ATM專用）
            $table->string('code_no')->nullable(); // ATM虛擬帳號（ATM專用）
            $table->date('expire_date')->nullable(); // ATM繳費期限（ATM專用）
            $table->text('raw_response')->nullable(); // 藍新回傳原始資料
            $table->timestamp('paid_at')->nullable(); // 付款完成時間
            $table->timestamps(); // 建立時間、更新時間

            $table->foreign('new_ebpay_order_id')->references('id')->on('new_ebpay_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('new_ebpay_payments');
    }
};
