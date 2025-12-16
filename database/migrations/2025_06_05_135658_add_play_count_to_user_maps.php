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
            $table->integer('play_count')->default(0)->after('is_deleted')->comment('遊玩次數');
            $table->integer('pass_count')->default(0)->after('play_count')->comment('通關次數');
            $table->integer('like_count')->default(0)->after('play_count')->comment('喜歡次數');
            $table->integer('favorite_count')->default(0)->after('like_count')->comment('收藏次數');
            $table->integer('is_featured')->default(0)->after('favorite_count')->comment('是否精選');
            $table->boolean('is_recommend')->default(0)->after('is_featured')->comment('是否推薦');
            $table->integer('view_count')->default(0)->after('is_recommend')->comment('瀏覽次數');

           if (Schema::hasColumn('user_maps', 'map_type')) {
                $table->dropColumn('map_type');
            }


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_maps', function (Blueprint $table) {
            $table->dropColumn([
                'play_count',
                'pass_count',
                'like_count',
                'favorite_count',
                'is_featured',
                'is_recommend',
            ]);

           

            if (! Schema::hasColumn('user_maps', 'map_type')) {
                $table->string('map_type')->nullable()->after('player_num')->comment('地圖類型，1:闖關, 2:競速, 3:生存');
            }
        });
    }
};
