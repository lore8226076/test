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
        Schema::create('user_pay_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('uid')->index()->comment('遊戲內玩家 ID');
            $table->string('order_id')->unique()->comment('平台訂單 ID');
            $table->string('package_id')->comment('平台商品 ID');
            $table->string('transaction_id')->nullable()->comment('Google/Apple 訂單 ID');
            $table->decimal('amount', 10, 2)->comment('交易金額');
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending')->comment('交易狀態: pending, success, failed, refunded');
            $table->enum('payment_method', ['apple', 'google'])->comment('交易方式: apple, google');
            $table->string('currency', 3)->default('TWD');
            $table->text('purchase_token')->nullable()->comment('Google Play: purchaseToken / Apple: transactionReceipt'); // 交易憑證，允許 NULL
            $table->string('error_message', 500)->nullable()->comment('失敗訊息');
            $table->timestamps();

            // 建立外鍵
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pay_orders');
    }
};
