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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('teaching_name')->default(0)->after('teaching_level')->comment('取名是否完成');
            $table->boolean('teaching_move')->default(0)->after('teaching_name')->comment('移動是否完成');
            $table->boolean('teaching_map_room')->default(0)->after('teaching_move')->comment('地編第一次拉房間是否完成');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function($table) {
            $table->dropColumn('teaching_name');
            $table->dropColumn('teaching_move');
            $table->dropColumn('teaching_map_room');
        });
    }
};
