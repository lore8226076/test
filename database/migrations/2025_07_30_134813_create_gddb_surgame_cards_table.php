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
        if (Schema::hasTable('gddb_surgame_cards')) {
            Schema::dropIfExists('gddb_surgame_cards');
        }
        Schema::create('gddb_surgame_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->comment('名稱');
            $table->string('desc', 255)->nullable()->comment('描述');
            $table->string('sprite_name', 64)->nullable()->comment('精靈名稱');
            $table->string('card_type', 32)->nullable()->comment('卡片類型');
            $table->integer('owner_hero_id')->nullable()->comment('擁有者英雄ID');
            $table->integer('synergy_hero_id')->nullable()->comment('協同英雄ID');
            $table->string('modifiers', 255)->nullable()->comment('修飾詞/附加效果');
            $table->integer('num')->default(0)->comment('數量');

            $table->comment('天賦卡片');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_cards');
    }
};
