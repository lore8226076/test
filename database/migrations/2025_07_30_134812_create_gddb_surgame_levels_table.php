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
        if (Schema::hasTable('gddb_surgame_levels')) {
            Schema::dropIfExists('gddb_surgame_levels');
        }
        Schema::create('gddb_surgame_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->comment('群組ID');
            $table->unsignedInteger('level')->comment('等級');
            $table->unsignedInteger('base_atk')->default(0)->comment('基礎ATK');
            $table->unsignedInteger('base_hp')->default(0)->comment('基礎HP');
            $table->unsignedInteger('base_def')->default(0)->comment('基礎DEF');
            $table->comment('代號說明表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_levels');
    }
};
