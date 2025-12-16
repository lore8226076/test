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
        Schema::create('user_slot_equipments', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->comment('使用者 ID');
            $table->integer('slot_id')->comment('陣位 ID');
            $table->integer('position')->comment('位置 0~4');
            $table->integer('refine_level')->default(1)->comment('精煉等級');
            $table->integer('enhance_level')->default(1)->comment('強化等級');
            $table->unique(['uid', 'slot_id', 'position'], 'uid_slot_position_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_slot_equipments');
    }
};
