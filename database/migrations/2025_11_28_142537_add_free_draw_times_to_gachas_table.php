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
        Schema::table('gachas', function (Blueprint $table) {
            $table->unsignedInteger('free_draw_times')->default(0)->after('max_times')->comment('獎池免費抽取次數');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gachas', function (Blueprint $table) {
            $table->dropColumn('free_draw_times');
        });
    }
};
