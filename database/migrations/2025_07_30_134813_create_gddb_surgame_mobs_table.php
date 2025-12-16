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
        if (Schema::hasTable('gddb_surgame_mobs')) {
            Schema::dropIfExists('gddb_surgame_mobs');
        }
        Schema::create('gddb_surgame_mobs', function (Blueprint $table) {
            $table->id();
            $table->string('prefab')->nullable()->comment('介面名稱'); // 介面名稱
            $table->integer('hp')->default(0)->comment('生命值'); // 生命值
            $table->integer('atk')->default(0)->comment('攻擊力'); // 攻擊力
            $table->integer('def')->default(0)->comment('防禦力'); // 防禦力
            $table->integer('exp')->default(0)->comment('經驗值'); // 經驗值
            $table->integer('gold')->default(0)->comment('金幣'); // 金幣

            $table->comment('怪物資訊');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_mobs');
    }
};
