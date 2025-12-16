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
        Schema::create('user_equipment_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->index()->comment('使用者ID');
            $table->integer('slot_id')->nullable()->comment('使用的陣位id');
            $table->integer('item_id')->comment('裝備道具ID');
            $table->integer('is_used')->default(0)->comment('是否已使用');
            $table->integer('position')->nullable()->comment('裝備順序位置');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_equipment_sessions');
    }
};
