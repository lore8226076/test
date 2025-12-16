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
        if (Schema::hasTable('gddb_surgame_talent_draw')) {
            Schema::dropIfExists('gddb_surgame_talent_draw');
        }
        Schema::create('gddb_surgame_talent_draw', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_lv')->comment('帳號等級');
            $table->unsignedInteger('cost')->comment('消耗道具ID');
            $table->unsignedInteger('amount')->default(0)->comment('消耗數量');
            $table->text('card_pool')->nullable()->comment('抽卡卡池');
            $table->comment('天賦抽卡消耗與卡池表');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_talent_draw');
    }
};
