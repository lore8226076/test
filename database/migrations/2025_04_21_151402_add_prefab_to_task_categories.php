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
        Schema::table('task_categories', function (Blueprint $table) {
            $table->longText('show_type')->nullable()->comment('顯示類型');
            $table->string('show_page_prefab')->nullable()->comment('頁面預設名稱');
            $table->integer('bonus_task_start_id')->nullable()->comment('BonusTask開始ID');
            $table->integer('bonus_task_end_id')->nullable()->comment('BonusTask結束ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_categories', function (Blueprint $table) {
            $table->dropColumn('show_type');
            $table->dropColumn('show_page_prefab');
            $table->dropColumn('bonus_task_start_id');
            $table->dropColumn('bonus_task_end_id');
        });
    }
};
