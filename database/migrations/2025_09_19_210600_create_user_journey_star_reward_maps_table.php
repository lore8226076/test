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
        Schema::create('user_journey_star_reward_maps', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->comment('玩家 UID');
            $table->unsignedBigInteger('reward_unique_id')->comment('星級獎勵 unique_id');
            $table->unsignedTinyInteger('is_received')->default(0)->comment('是否已領取');
            $table->timestamps();

            $table->unique(['uid', 'reward_unique_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_journey_star_reward_maps');
    }
};
