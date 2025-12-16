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
        Schema::table('user_surgame_infos', function (Blueprint $table) {
            $table->integer('grade_level')->default(1)->comment('軍階等級');
            $table->index(['uid', 'grade_level'], 'idx_uid_grade_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_surgame_infos', function (Blueprint $table) {
            $table->dropColumn('grade_level');
            $table->dropIndex('idx_uid_grade_level');
        });
    }
};
