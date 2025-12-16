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
        Schema::create('user_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique()->comment('使用者ID');
            $table->integer('stamina')->default(200);
            $table->integer('stamina_max')->default(200);
            $table->timestamp('next_recover_at')->nullable()->comment('下次恢復時間');

            // 掃蕩
            $table->integer('sweep_count')->default(10)->comment('掃蕩次數');
            $table->integer('sweep_max')->default(10)->comment('掃蕩最大次數');

            // 禁止進入的關卡
            $table->json('forbidden_stages')->nullable()->comment('禁止進入的關卡');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_statuses');
    }
};
