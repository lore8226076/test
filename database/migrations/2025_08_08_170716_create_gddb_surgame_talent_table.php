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
        if (Schema::hasTable('gddb_surgame_talent')) {
            Schema::dropIfExists('gddb_surgame_talent');
        }

        Schema::create('gddb_surgame_talent', function (Blueprint $table) {
            $table->id();
            $table->string('card_id', 50)->comment('卡片ID');
            $table->unsignedInteger('lv')->comment('天賦等級');
            $table->string('icon', 255)->nullable()->comment('圖標路徑');
            $table->string('name', 100)->comment('天賦名稱');
            $table->string('description', 255)->nullable()->comment('天賦描述');
            $table->string('func', 50)->comment('功能類型');
            $table->unsignedInteger('parament')->comment('功能參數');
            $table->comment('天賦卡片資料表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_talent');
    }
};
