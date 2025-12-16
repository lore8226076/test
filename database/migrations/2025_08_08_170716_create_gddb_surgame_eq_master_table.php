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
        if (Schema::hasTable('gddb_surgame_eq_master')) {
            Schema::dropIfExists('gddb_surgame_eq_master');
        }

        Schema::create('gddb_surgame_eq_master', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->comment('大師類型');
            $table->unsignedInteger('lv')->comment('等級');
            $table->unsignedInteger('necessary_min_lv')->default(0)->comment('所需最低等級');
            $table->unsignedInteger('atk_bonus')->default(0);
            $table->unsignedInteger('hp_bonus')->default(0);
            $table->unsignedInteger('def_bonus')->default(0);
            $table->comment('裝備大師加成表');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_eq_master');
    }
};
