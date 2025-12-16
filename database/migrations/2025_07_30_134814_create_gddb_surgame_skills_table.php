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
        if (Schema::hasTable('gddb_surgame_skills')) {
            Schema::dropIfExists('gddb_surgame_skills');
        }
        Schema::create('gddb_surgame_skills', function (Blueprint $table) {
            $table->id();
            $table->string('ui_sprite_name')->nullable()->comment('介面圖案名稱'); // 介面圖案名稱
            $table->string('prefab')->nullable()->comment('介面名稱'); // 介面名稱

            $table->comment('技能圖示');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_skills');
    }
};
