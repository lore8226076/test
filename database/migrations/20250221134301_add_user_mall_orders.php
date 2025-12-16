<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_mall_orders', function (Blueprint $table) {
            $table->integer('item_price_id')->after('item_id');
            $table->integer('currency_item_id')->after('currency_type');
            $table->dropColumn('currency_type');

        });

        // 設定表格屬性
        DB::statement("ALTER TABLE user_mall_orders COLLATE='utf8mb4_unicode_ci'");
        DB::statement("ALTER TABLE user_mall_orders ENGINE=InnoDB");
        DB::statement("ALTER TABLE user_mall_orders ROW_FORMAT=DYNAMIC");
        DB::statement("ALTER TABLE user_mall_orders AUTO_INCREMENT=25");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_mall_orders', function (Blueprint $table) {
            $table->dropColumn('item_price_id');
            $table->string('currency_type')->after('currency_item_id');
            $table->dropColumn('currency_item_id');
        });

    }
};
