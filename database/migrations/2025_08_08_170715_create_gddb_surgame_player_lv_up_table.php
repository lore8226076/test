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
        if (Schema::hasTable('gddb_surgame_player_lv_up')) {
            Schema::dropIfExists('gddb_surgame_player_lv_up');
        }

        Schema::create('gddb_surgame_player_lv_up', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_lv')->comment('帳號等級');
            $table->unsignedInteger('xp')->default(0)->comment('升級所需經驗值');
            $table->comment('玩家等級與升級經驗表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_player_lv_up');
    }
};
