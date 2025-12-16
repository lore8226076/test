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
        Schema::create('user_stages', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->comment('使用者ID');
            $table->integer('stage_id')->comment('關卡ID');
            $table->integer('is_clear')->default(0)->comment('是否通關');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stages');
    }
};
