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
            $table->string('check_id')->after('condition')->nullable()->comment("檢查任務內容的 ID");
        });

       // 移除user_id
       Schema::table('user_tasks', function (Blueprint $table) {
        $table->dropColumn('user_id');
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('check_id');
        });

        Schema::table('user_tasks', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
