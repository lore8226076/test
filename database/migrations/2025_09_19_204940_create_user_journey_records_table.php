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
        Schema::create('user_journey_records', function (Blueprint $table) {
            $table->id();
            // int uid, 當前章節, 當前波次, 當前星數
            $table->integer('uid')->unique();
            $table->unsignedInteger('current_journey_id')->default(0);
            $table->unsignedInteger('current_wave')->default(0);
            $table->unsignedInteger('total_stars')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_journey_records');
    }
};
