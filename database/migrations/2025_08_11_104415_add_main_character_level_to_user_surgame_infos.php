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
        // 如果欄位已存在，先移除再新增
        if (Schema::hasColumn('user_surgame_infos', 'main_character_level')) {
            Schema::table('user_surgame_infos', function (Blueprint $table) {
                $table->dropIndex(['uid', 'main_chapter']);
                $table->dropColumn('main_character_level');
            });
        }

        Schema::table('user_surgame_infos', function (Blueprint $table) {
            $table->integer('main_character_level')->default(1)->comment('主角等級')->after('main_chapter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_surgame_infos', function (Blueprint $table) {
            $table->dropIndex(['uid', 'main_chapter']);
            $table->dropColumn('main_character_level');
        });
    }
};
