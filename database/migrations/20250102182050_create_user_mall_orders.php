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
        Schema::create('user_mall_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->integer('uid')->index();
            $table->integer('item_id');
            $table->integer('qty');
            $table->double('price');
            $table->double('total_price');
            $table->string('currency_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_mall_orders');
    }
};
