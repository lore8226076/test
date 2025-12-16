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
        Schema::create('new_ecpay_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ecpay_order_id');
            $table->string('method');                  // Credit, ATM, etc
            $table->integer('amount');
            $table->string('status')->default('success'); // success / fail
            $table->string('trade_no')->nullable();     // ECPay 交易編號
            $table->string('merchant_order_no')->nullable();
            $table->string('bank_code')->nullable();    // ATM銀行代碼
            $table->string('code_no')->nullable();      // ATM 虛擬帳號
            $table->date('expire_date')->nullable();    // ATM繳費期限
            $table->timestamp('paid_at')->nullable();
            $table->text('raw_response')->nullable();
            $table->timestamps();

            $table->foreign('ecpay_order_id')->references('id')->on('new_ecpay_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_ecpay_payments');
    }
};
