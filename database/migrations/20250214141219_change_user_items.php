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
        Schema::table('user_items', function (Blueprint $table) {
            // 增加索引
            $table->index('item_id');  // 單欄索引
            $table->index('is_lock');  // 單欄索引
            $table->index(['user_id', 'item_id', 'is_lock']); // 複合索引（適用於經常一起查詢）
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_items', function (Blueprint $table) {
            // 移除索引
            $table->dropIndex(['item_id']);
            $table->dropIndex(['is_lock']);
            $table->dropIndex(['user_id', 'item_id', 'is_lock']); // 刪除複合索引
        });
    }
};
