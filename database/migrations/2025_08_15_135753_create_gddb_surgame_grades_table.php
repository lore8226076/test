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
        if (Schema::hasTable('gddb_surgame_grades')) {
            Schema::dropIfExists('gddb_surgame_grades');
        }
        Schema::create('gddb_surgame_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('unique_id')->comment('uuid');
            $table->string('grade_group', 50)->comment('軍階群組');
            $table->unsignedTinyInteger('grade_level')->comment('軍階等級');
            $table->string('grade_name', 50)->comment('軍階名稱');
            $table->string('reward', 64)->nullable()->default('')->comment('軍階獎勵');
            $table->string('func_key', 16)->nullable()->default('')->comment('功能鍵值');
            $table->string('func_desc', 100)->nullable()->default('')->comment('功能說明');
            $table->string('quests', 64)->nullable()->default('')->comment('對應任務');
            $table->integer('related_level')->default(1)->comment('對應等級');
            $table->unique(['grade_group', 'grade_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_grades');
    }
};
