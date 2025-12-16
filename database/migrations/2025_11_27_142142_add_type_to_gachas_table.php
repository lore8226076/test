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
        Schema::table('gachas', function (Blueprint $table) {
            // 1:英雄, 2:寶物, 3:造型, 4:活動
            $table->tinyInteger('type')->default(1)->after('name')->comment('抽卡類型 1:英雄 2:寶物 3:造型 4:活動');
        });
        Schema::table('user_gacha_orders', function (Blueprint $table) {
            // 1:英雄, 2:寶物, 3:造型, 4:活動
            $table->tinyInteger('type')->default(1)->after('gacha_id')->comment('抽卡類型 1:英雄 2:寶物 3:造型 4:活動');
            // 此抽為免費抽
            $table->boolean('is_free')->default(false)->after('type')->comment('是否為免費抽');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gachas', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('user_gacha_orders', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('is_free');
        });
    }
};
