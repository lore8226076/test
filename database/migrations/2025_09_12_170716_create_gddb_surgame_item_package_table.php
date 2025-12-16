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
        if (Schema::hasTable('gddb_surgame_item_package')) {
            Schema::dropIfExists('gddb_surgame_item_package');
        }

        Schema::create('gddb_surgame_item_package', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id')->comment('物品ID');
            $table->integer('manager_id')->comment('表專屬ID');
            $table->integer('auto_use')->default(0)->comment('是否自動使用(0:否,1:是)');
            $table->integer('choice_box')->default(0)->comment('是否為選擇寶箱(0:隨機,1:自選)');
            $table->integer('random_times')->default(0)->comment('隨機次數(0:全拿,1:隨機一次)');
            $table->integer('use_necessary')->default(1)->comment('使用所需數量');
            $table->longText('contents')->comment('內容物');
            $table->string('note', 255)->nullable()->comment('備註');
            $table->comment('Surgame寶箱表');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_item_package');
    }
};
