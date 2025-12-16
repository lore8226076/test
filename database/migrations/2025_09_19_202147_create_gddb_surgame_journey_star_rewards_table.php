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
        if (Schema::dropIfExists('gddb_surgame_journey_star_rewards')) {
            $table->drop('gddb_surgame_journey_star_rewards');
        }

        Schema::create('gddb_surgame_journey_star_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unique_id')->unique();
            $table->string('type')->nullable()->comment('對應系統');
            $table->string('star_count')->nullable()->comment('星數');
            $table->string('rewards')->nullable()->comment('獎勵物品');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_star_rewards');
    }
};
