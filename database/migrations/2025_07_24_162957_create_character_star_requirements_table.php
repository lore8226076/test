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
        Schema::create('character_star_requirements', function (Blueprint $table) {
            $table->id();
            $table->integer('character_id')->comment('角色id');
            $table->integer('star_level')->comment('星等');
            $table->integer('base_item_id')->comment('花費道具');
            $table->unsignedInteger('base_item_amount')->default(0)->comment('道具需求數量');
            $table->integer('extra_item_id')->comment('額外花費道具');
            $table->unsignedInteger('extra_item_amount')->default(0)->comment('額外道具需求數量');
            $table->integer('atk_growth')->default(0)->comment('攻擊成長');
            $table->integer('hp_growth')->default(0)->comment('血量成長');
            $table->integer('def_growth')->default(0)->comment('防禦成長');
            $table->string('memo')->nullable()->comment('筆記');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_star_requirements');
    }
};
