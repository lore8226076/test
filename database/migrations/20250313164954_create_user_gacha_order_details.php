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
        Schema::create('user_gacha_order_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_gacha_order_id'); // 關聯 user_gacha_orders
            $table->integer('item_id'); // 抽到的物品 ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_gacha_order_details');
    }
};
