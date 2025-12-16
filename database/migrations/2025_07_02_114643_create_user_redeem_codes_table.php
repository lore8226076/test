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
        Schema::create('user_redeem_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('uid')->comment('使用者 ID');
            $table->unsignedBigInteger('redeem_code_id')->comment('兌換碼 ID');
            $table->timestamp('redeemed_at')->nullable()->comment('兌換時間');
            $table->string('reward_snapshot', 255)->nullable()->comment('兌換當下的獎勵內容(備查用)');
            $table->timestamps();
            $table->unique(['uid', 'redeem_code_id']); // 一個user一張碼只能換一次
            $table->softDeletes();
            $table->index('uid');
            $table->index('redeem_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_redeem_codes');
    }
};
