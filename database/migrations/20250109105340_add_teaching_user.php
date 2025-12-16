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
            $table->boolean('teaching_square')->default(0)->after('is_active')->comment('廣場教學是否完成');
            $table->boolean('teaching_level')->default(0)->after('teaching_square')->comment('關卡是否完成');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function($table) {
            $table->dropColumn('teaching_square');
            $table->dropColumn('teaching_level');
        });
    }
};
