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
        Schema::create('gddb_surgame_daily_resource', function (Blueprint $table) {
            $table->id();
            $table->integer('unique_id')->comment('資料ID');
            $table->string('type')->comment('類型');
            $table->integer('stage_id')->comment('場景資料');
            $table->integer('difficulty')->comment('難度');
            $table->integer('stage_lv')->comment('關卡等級');
            $table->text('first_reward')->comment('首通獎勵');
            $table->text('reward')->comment('獎勵');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_daily_resource');
    }
};
