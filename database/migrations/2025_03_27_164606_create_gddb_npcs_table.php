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
        Schema::create('gddb_npcs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enemy_id'); // 對應原始 "//id"
            $table->string('prefab');
            $table->float('search_dist');
            $table->float('strafe_min');
            $table->float('strafe_max');
            $table->unsignedInteger('hp');
            $table->unsignedInteger('bp');
            $table->float('atk');
            $table->unsignedInteger('def');
            $table->unsignedInteger('brk');
            $table->unsignedInteger('lv');
            $table->unsignedInteger('exp');
            $table->unsignedInteger('gold');
            $table->string('skills'); // 逗號分隔 暫當 string 存
            $table->float('trigger_radius');
            $table->unsignedInteger('killedscore');
            $table->timestamps();
            $table->unique(['enemy_id', 'prefab'], 'enemy_id_prefab_unique');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_npcs');
    }
};
