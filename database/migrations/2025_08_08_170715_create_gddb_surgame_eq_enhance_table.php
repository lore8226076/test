<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gddb_surgame_eq_enhance')) {
            Schema::dropIfExists('gddb_surgame_eq_enhance');
        }
        Schema::create('gddb_surgame_eq_enhance', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('lv')->comment('強化等級');
            $table->unsignedInteger('min_slot_lv')->default(0)->comment('最低槽位等級');
            $table->unsignedInteger('cost')->comment('主要消耗道具ID');
            $table->unsignedInteger('cost_amount')->default(0)->comment('主要消耗數量');
            $table->unsignedInteger('ex_cost')->comment('額外消耗道具ID');
            $table->unsignedInteger('ex_cost_amount')->default(0)->comment('額外消耗數量');
            $table->comment('裝備強化消耗表');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_eq_enhance');
    }
};
