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
        Schema::create('map_favorites', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->comment('使用者ID');
            $table->integer('map_id')->comment('地圖ID');
            $table->timestamps();

            $table->comment('地圖收藏');
            $table->unique(['uid', 'map_id']); // 使用者只能收藏一次
            $table->index(['uid', 'map_id']); // 使用者收藏的地圖
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_favorites');
    }
};
