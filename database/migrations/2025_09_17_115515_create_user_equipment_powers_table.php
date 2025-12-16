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
        Schema::create('user_equipment_powers', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->comment('使用者ID');
            $table->integer('equipment_id')->comment('裝備ID');
            $table->integer('position')->comment('裝備位置');
            $table->integer('power')->comment('戰力');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_equipment_powers');
    }
};
