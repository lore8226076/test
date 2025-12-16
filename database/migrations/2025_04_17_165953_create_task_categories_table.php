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
        Schema::create('task_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('任務分類名稱');
            $table->string('localization_name')->nullable()->comment('任務分類名稱(多國語系)');
            $table->integer('is_active')->comment('是否啟用');
            $table->timestamps();
        });

        // 移除tasks表的type欄位
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('type');

            // 新增type_id欄位
            $table->foreignId('type_id')->after('id')->comment('任務分類ID');

            $table->string('localization_name')->after('type_id')->nullable()->comment('任務名稱(多國語系)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_categories');

        // 新增tasks表的type欄位
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('type_id');
            $table->dropColumn('localization_name');

            $table->string('type')->comment('任務分類');

        });
    }
};
