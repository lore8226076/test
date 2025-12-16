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
        Schema::create('new_ecpay_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no')->unique();        // MerchantTradeNo
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('amount');
            $table->string('item_desc');
            $table->string('email');
            $table->string('status')->default('pending'); // pending / paid / failed
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_ecpay_orders');
    }
};
