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
        Schema::create('user_talent_pool_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->comment('使用者uid');
            $table->integer('talent_draw_id')->comment('獎池id');
            $table->integer('level_at_bind')->comment('獎池對應等級');
            $table->longText('current_remaining')->comment('尚未抽取的獎勵');
            $table->enum('status', ['active', 'completed'])->default('active')->comment('獎池進度');
            $table->timestamps();

            $table->unique(['uid', 'talent_draw_id'], 'uid_draw_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_talent_pool_sessions');
    }
};
