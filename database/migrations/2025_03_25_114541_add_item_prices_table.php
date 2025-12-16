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
            $table->integer('discount_percentage')->after('price')->default(0)->comment('折扣比例（整數）');
            $table->integer('price_after_discount')->after('discount_percentage')->nullable()->comment('折扣後的價格（整數）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_prices', function (Blueprint $table) {
            $table->dropColumn(['discount_percentage', 'price_after_discount']);
        });
    }
};
