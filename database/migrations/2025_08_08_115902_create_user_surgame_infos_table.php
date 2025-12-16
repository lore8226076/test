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
        if (Schema::hasTable('user_surgame_infos')) {
            Schema::dropIfExists('user_surgame_infos');
        }
        Schema::create('user_surgame_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->unique()->comment('玩家uid');
            $table->integer('main_chapter')->default(1)->comment('人物當前關卡');
            $table->index(['uid', 'main_chapter'], 'user_and_main_chapter');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_surgame_infos');
    }
};
