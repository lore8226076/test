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
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->string('name')->comment('任務名稱');
                $table->string('type')->comment('任務類型'); // daily, weekly, events, special etc.
                $table->json('condition')->comment('任務條件');
                $table->json('reward')->comment('任務獎勵');
                $table->datetime('start_at')->nullable()->comment('任務開始時間');
                $table->datetime('end_at')->nullable()->comment('任務結束時間');
                $table->boolean('repeatable')->default(false)->comment('是否可重複');  //0 不重置, 1 每日,2 每週,3 每月, -1 領獎後立即重置
                $table->boolean('is_active')->default(true)->comment('是否啟用');
                $table->timestamps();
                $table->index('type');
                $table->comment('任務總表');
            });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
