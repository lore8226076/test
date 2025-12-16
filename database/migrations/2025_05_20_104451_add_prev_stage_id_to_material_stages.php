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
        Schema::table('material_stages', function (Blueprint $table) {
            $table->integer('prev_stage_id')->nullable()->comment('上一關卡ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_stages', function (Blueprint $table) {
            $table->dropColumn('prev_stage_id');
        });
    }
};
