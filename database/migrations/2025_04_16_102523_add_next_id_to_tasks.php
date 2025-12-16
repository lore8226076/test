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
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedInteger('prev_task_id')->after('end_at')->nullable()->comment('上一個id');
            $table->unsignedInteger('next_task_id')->after('prev_task_id')->nullable()->comment('下一個id');
            $table->boolean('is_auto_complete')->after('next_task_id')->default(false)->comment('是否自動完成');
            $table->datetime('start_trigger')->after('is_active')->nullable()->comment('開始觸發動作');
            $table->datetime('end_trigger')->after('start_trigger')->nullable()->comment('結束觸發動作');

            // 移除name
            $table->dropColumn('name');

            // 修改repeatable 為 repeatable_type
            $table->dropColumn('repeatable');
            $table->string('repeatable_type')->after('is_active')->nullable()->comment('重複類型');
        });

        Schema::table('user_tasks', function (Blueprint $table) {
            $table->datetime('completed_at')->after('status')->nullable()->comment('任務完成時間');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('prev_task_id');
            $table->dropColumn('next_task_id');
            $table->dropColumn('is_auto_complete');
            $table->dropColumn('start_trigger');
            $table->dropColumn('end_trigger');
            $table->dropColumn('repeatable_type');
            // 新增name
            $table->string('name')->after('id');
            $table->boolean('repeatable')->after('is_active');
        });

        Schema::table('user_tasks', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
