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
        Schema::table('item_prices', function (Blueprint $table) {
            // 數量
            $table->integer('qty')->default(1);
            // 商城商品id
            $table->string('product_id')->nullable()->comment('商城商品id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_prices', function (Blueprint $table) {
            $table->dropColumn('qty');
            $table->dropColumn('product_id');
        });
    }
};
