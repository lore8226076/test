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
        if (Schema::hasTable('gddb_surgame_level_ups')) {
            Schema::dropIfExists('gddb_surgame_level_ups');
        }
        Schema::create('gddb_surgame_level_ups', function (Blueprint $table) {
            $table->id();
            $table->integer('target_level')->unique()->comment('目標等級');
            $table->integer('base_item_id')->comment('花費道具');
            $table->unsignedInteger('base_item_amount')->default(0)->comment('道具需求數量');
            $table->integer('extra_item_id')->nullable()->comment('額外花費道具');
            $table->unsignedInteger('extra_item_amount')->default(0)->comment('額外道具需求數量');

            $table->comment('等級提升內容資料表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_level_ups');
    }
};
