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
        Schema::create('level_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('level')->comment('等級');
            $table->integer('cost_item_id')->comment('花費道具');
            $table->unsignedInteger('cost_amount')->default(0)->comment('道具需求數量');
            $table->integer('ex_cost_item_id')->comment('額外花費道具');
            $table->unsignedInteger('ex_cost_amount')->default(0)->comment('額外道具需求數量');
            $table->string('memo')->nullable()->comment('筆記');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_levels');
    }
};
