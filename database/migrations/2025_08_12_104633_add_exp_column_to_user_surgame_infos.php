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
        Schema::table('user_surgame_infos', function (Blueprint $table) {
            $table->integer('current_exp')->default(0)->comment('經驗值');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_surgame_infos', function (Blueprint $table) {
            $table->dropColumn('current_exp');
        });
    }
};
