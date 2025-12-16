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
        Schema::create('map_likes', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->comment('使用者ID');
            $table->integer('map_id')->comment('地圖ID');
            $table->timestamps();

            $table->comment('地圖按讚');
            $table->unique(['uid', 'map_id']); // 使用者只能按讚一次
            $table->index(['uid', 'map_id']); // 使用者按讚的地圖
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_likes');
    }
};
