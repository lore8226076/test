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
            if (Schema::dropIfExists('gddb_surgame_journey_rewards')) {
            $table->drop('gddb_surgame_journey_rewards');
        }
        Schema::create('gddb_surgame_journey_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journey_id')->comment('關卡ID');
            $table->integer('wave')->default(0)->comment('波次');
            $table->string('rewards')->nullable()->comment('獎勵物品');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_journey_rewards');
    }
};
