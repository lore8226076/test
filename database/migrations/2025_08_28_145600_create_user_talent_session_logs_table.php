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
        Schema::create('user_talent_session_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->comment('玩家uid');
            $table->integer('session_id')->comment('抽獎紀錄id');
            $table->string('item_code')->comment('獎勵');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_talent_session_logs');
    }
};
