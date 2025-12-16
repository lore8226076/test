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
        if (Schema::hasTable('user_patrol_rewards')) {
            Schema::dropIfExists('user_patrol_rewards');
        }
        Schema::create('user_patrol_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->unique()->comment('玩家ID');
            $table->timestamp('last_claimed_at')->nullable()->comment('最後領獎時間');
            $table->unsignedTinyInteger('pending_minutes')->default(0)->comment('累積未滿10分鐘的時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_patrol_rewards');
    }
};
