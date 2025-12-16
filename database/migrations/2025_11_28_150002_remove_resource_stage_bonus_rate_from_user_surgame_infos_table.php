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
            $table->dropColumn('resource_stage_bonus_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_surgame_infos', function (Blueprint $table) {
            $table->integer('resource_stage_bonus_rate')->default(0)->comment('獎勵加成比例(%)');
        });
    }
};
