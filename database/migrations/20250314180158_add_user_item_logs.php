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
        Schema::table('user_item_logs', function (Blueprint $table) {
            $table->integer('user_gacha_order_id')->nullable()->after('user_mall_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_item_logs', function (Blueprint $table) {
            $table->dropColumn('user_gacha_order_id');
        });
    }
};
