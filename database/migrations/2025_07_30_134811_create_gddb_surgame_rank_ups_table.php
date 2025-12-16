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
        if (Schema::hasTable('gddb_surgame_rank_ups')) {
            Schema::dropIfExists('gddb_surgame_rank_ups');
        }
        Schema::create('gddb_surgame_rank_ups', function (Blueprint $table) {
            $table->id();
            $table->integer('group_id')->commit('群組識別號碼');
            $table->integer('character_id')->nullable()->default(null)->comment('角色id');
            $table->integer('star_level')->comment('星等');
            $table->integer('base_item_id')->comment('花費道具');
            $table->unsignedInteger('base_item_amount')->default(0)->comment('道具需求數量');
            $table->integer('extra_item_id')->nullable()->comment('額外花費道具');
            $table->unsignedInteger('extra_item_amount')->default(0)->comment('額外道具需求數量');

            $table->comment('星級提升內容資料表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_rank_ups');
    }
};
