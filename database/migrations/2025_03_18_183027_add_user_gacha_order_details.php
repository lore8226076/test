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
        Schema::table('user_gacha_order_details', function (Blueprint $table) {
            $table->boolean('is_change')->after('item_id')->comment('重複道具，轉成券');
            $table->integer('change_item_id')->after('is_change')->nullable()->comment('轉換的道具ID');
            $table->integer('change_qty')->after('change_item_id')->nullable()->comment('轉換的道具數量');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_gacha_order_details', function($table) {
            $table->dropColumn('is_change');
            $table->dropColumn('change_item_id');
            $table->dropColumn('change_qty');
        });
    }
};
