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
        if (Schema::hasTable('gddb_surgame_passive_rewards')) {
            Schema::dropIfExists('gddb_surgame_passive_rewards');
        }
        Schema::create('gddb_surgame_passive_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('now_stage')->index();
            $table->text('rand_reward')->nullable();
            $table->unsignedInteger('hour_coin')->default(0);
            $table->unsignedInteger('hour_exp')->default(0);
            $table->unsignedInteger('hour_crystal')->default(0);
            $table->unsignedInteger('hour_paint')->default(0);
            $table->unsignedInteger('hour_xp')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_passive_rewards');
    }
};
