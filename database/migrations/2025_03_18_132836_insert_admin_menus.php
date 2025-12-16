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
        DB::table('admin_menus')->insert([
            'id'           => 2990,  // 假設這是手動指定的 ID
            'up_id'        => 2000,  // 父級菜單 ID，0 代表頂級菜單
            'sort'         => 0,  // 排序值，決定顯示順序
            'name'         => '重複換券價值',
            'link'         => 'admin.gacha.setting_to_ticket',
            'icon'         => null, // 字體圖標
            'blank'        => false, // 是否新視窗開啟
            'is_dashboard' => false, // 是否為 Dashboard
            'is_active'    => true, // 是否啟用
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('admin_menus')->whereIn('id', [2990])->delete();
    }

};
