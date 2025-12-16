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
        if (Schema::hasTable('gddb_surgame_rank_funcs')) {
            Schema::dropIfExists('gddb_surgame_rank_funcs');
        }
        Schema::create('gddb_surgame_rank_funcs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->comment('群組ID');
            $table->unsignedInteger('required_star_rank')->comment('要求星等');
            $table->string('name', 64)->comment('效果名稱');
            $table->string('description', 255)->nullable()->comment('效果說明');
            $table->string('type', 32)->nullable()->comment('效果類型');
            $table->string('func_data', 255)->nullable()->comment('功能資料');
            $table->integer('atk_grow')->default(0)->comment('攻擊成長');
            $table->integer('hp_grow')->default(0)->comment('血量成長');
            $table->integer('def_grow')->default(0)->comment('防禦成長');

            $table->comment('星級技能');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_rank_funcs');
    }
};
