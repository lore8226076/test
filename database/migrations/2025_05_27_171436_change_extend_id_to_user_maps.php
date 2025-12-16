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
        Schema::table('user_maps', function (Blueprint $table) {
            // 移除extend_id
            $table->dropColumn('extend_id');
            $table->string('draft_id')->nullable()->after('updated_at')->comment('草稿id');
            $table->string('map_uuid')->nullable()->after('updated_at')->comment('地圖編號');
            $table->json('map_tags')->nullable()->after('updated_at')->comment('地圖標籤');
            $table->integer('play_time')->nullable()->after('updated_at')->comment('遊玩時長');
            $table->string('map_type')->nullable()->after('updated_at')->comment('地圖類型');
            $table->integer('player_num')->nullable()->after('updated_at')->comment('遊玩人數');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_maps', function (Blueprint $table) {
            $table->dropColumn('draft_id');
            $table->string('extend_id')->nullable()->after('id')->comment('草稿id');
            $table->dropColumn('map_uuid');
            $table->dropColumn('map_tags');
            $table->dropColumn('play_time');
            $table->dropColumn('map_type');
            $table->dropColumn('player_num');
        });
    }
};
