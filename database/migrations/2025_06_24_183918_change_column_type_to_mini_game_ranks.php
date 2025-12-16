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
            // score改為double
            $table->double('score')->change();
            // total_time改為double 
            $table->double('total_time')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mini_game_ranks', function (Blueprint $table) {
            // score改為int
            $table->integer('score')->change();
            // total_time改為int
            $table->integer('total_time')->change();
        });
    }
};
