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
        Schema::create('user_tasks', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->nullable();
                $table->string('uid')->index()->comment('遊戲內玩家 ID');
                $table->integer('task_id')->nullable()->comment('任務 ID');
                $table->enum('status', ['in_progress', 'completed', 'failed'])->default('in_progress')->comment('任務狀態');
                $table->json('progress')->nullable()->comment('任務進度');
                $table->boolean('reward_status')->default(false)->comment('是否已領取獎勵');
                $table->timestamps();
                $table->index(['uid', 'task_id']);
                $table->comment('玩家任務表');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tasks');
    }
};
