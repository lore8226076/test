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
        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 255)->nullable()->collation('utf8mb4_general_ci')->index('uid')->comment('使用者 UID');
            $table->string('ip')->nullable()->comment('登入ip');
            $table->enum('methods', ['mac', 'uid', 'firebase','other'])->nullable()->comment('登入方式');
            $table->longText('login_data')->nullable()->comment('登入資料');
            $table->timestamps();

            $table->foreign('uid')->references('uid')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_login_logs');
    }
};
