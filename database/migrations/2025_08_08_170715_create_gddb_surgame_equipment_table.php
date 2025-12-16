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

        if (Schema::hasTable('gddb_surgame_equipment')) {
            Schema::dropIfExists('gddb_surgame_equipment');
        }

        Schema::create('gddb_surgame_equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('item_id')->nullable()->comment('裝備ID對應 gddb_items.item_id');
            $table->unsignedInteger('unique_id')->unique()->comment('裝備唯一ID對應 gddb_items.maneger_id');
            $table->string('type', 50)->comment('裝備槽位類型，例如 EQ_Slot01');
            $table->string('name', 100)->comment('裝備名稱 ID');
            $table->unsignedTinyInteger('quility')->comment('裝備品質等級');
            $table->unsignedInteger('base_atk')->default(0)->comment('基礎攻擊力');
            $table->unsignedInteger('base_hp')->default(0)->comment('基礎生命值');
            $table->unsignedInteger('base_def')->default(0)->comment('基礎防禦力');
            $table->comment('裝備基礎數值表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_equipment');
    }
};
