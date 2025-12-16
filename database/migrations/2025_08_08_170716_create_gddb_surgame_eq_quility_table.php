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
        if (Schema::hasTable('gddb_surgame_eq_quility')) {
            Schema::dropIfExists('gddb_surgame_eq_quility');
        }
        Schema::create('gddb_surgame_eq_quility', function (Blueprint $table) {
            $table->unsignedTinyInteger('quility')->primary()->comment('品質等級');
            $table->string('name', 100)->comment('品質名稱');
            $table->unsignedInteger('recycle_value')->default(0)->comment('回收價值');
            $table->unsignedTinyInteger('ex_attr_amount')->default(0)->comment('額外屬性數量');
            $table->string('ex_attr_atk', 50)->nullable()->comment('額外攻擊加成區間');
            $table->string('ex_attr_hp', 50)->nullable()->comment('額外生命加成區間');
            $table->string('ex_attr_def', 50)->nullable()->comment('額外防禦加成區間');
            $table->comment('裝備品質屬性表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_eq_quility');
    }
};
