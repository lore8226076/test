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
        Schema::table('mini_game_ranks', function (Blueprint $table) {
            $table->unique(['game_id', 'user_id'], 'unique_game_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mini_game_ranks', function (Blueprint $table) {
            $table->dropUnique('unique_game_user');
        });
    }
};
