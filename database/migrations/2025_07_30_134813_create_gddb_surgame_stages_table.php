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
        if (Schema::hasTable('gddb_surgame_stages')) {
             Schema::dropIfExists('gddb_surgame_stages');
        }
        Schema::create('gddb_surgame_stages', function (Blueprint $table) {
            $table->id();
            $table->string('map_key_name', 64)->comment('地圖鍵名');
            $table->string('scene_name', 64)->comment('場景名稱');
            $table->string('scene_logic', 255)->nullable()->comment('場景邏輯');
            $table->string('scene_ui', 255)->nullable()->comment('場景UI');

            $table->comment('關卡資訊');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_stages');
    }
};
