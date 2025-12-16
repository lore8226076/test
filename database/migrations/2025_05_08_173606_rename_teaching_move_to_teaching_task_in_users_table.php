<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 改欄位名稱
        DB::statement("ALTER TABLE `users` CHANGE `teaching_move` `teaching_task` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '任務介面教學是否完成'");
        DB::statement("ALTER TABLE `users` CHANGE `teaching_map_room` `teaching_mapeditor` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '地編教學是否完成'");

        // 新增欄位
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('teaching_pet')->default(0)->after('teaching_mapeditor')->comment('寵物教學是否完成');
            $table->boolean('teaching_levelselector')->default(0)->after('teaching_pet')->comment('關卡選擇教學是否完成');
            $table->boolean('teaching_maplobby')->default(0)->after('teaching_levelselector')->comment('地圖大廳教學是否完成');
            $table->boolean('teaching_gacha')->default(0)->after('teaching_maplobby')->comment('扭蛋教學是否完成');
        });
    }

    public function down(): void
    {
        // 改回原欄位名稱
        DB::statement("ALTER TABLE `users` CHANGE `teaching_task` `teaching_move` TINYINT(1) NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE `users` CHANGE `teaching_mapeditor` `teaching_map_room` TINYINT(1) NOT NULL DEFAULT 0");

        // 刪除欄位
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'teaching_pet',
                'teaching_levelselector',
                'teaching_maplobby',
                'teaching_gacha',
            ]);
        });
    }
};