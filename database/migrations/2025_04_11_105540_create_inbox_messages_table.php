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
        Schema::create('inbox_messages', function (Blueprint $table) {
            $table->id();
            $table->enum('sender_type', ['system', 'gm'])->comment('發送者類型');                                    // system: 系統, gm: GM
            $table->enum('target_type', ['all', 'single', 'batch'])->comment('接收者類型');                          // all: 全部, single: 單一, batch: 批量
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending')->comment('狀態'); // pending: 待發送, active: 已發送, expired: 已過期, cancelled: 已取消
            $table->string('title')->comment('標題');
            $table->text('content')->comment('內容');
            $table->dateTime('start_at')->nullable()->comment('開始時間');
            $table->dateTime('end_at')->nullable()->comment('結束時間');
            $table->dateTime('expire_at')->nullable()->comment('過期時間'); // 活動信件用
            $table->timestamps();

            $table->index('sender_type');
            $table->index('target_type');
            $table->index('status');
            $table->index('expire_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox_messages');
    }
};
