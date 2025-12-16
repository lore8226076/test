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
            $table->integer('max_times')->after('is_active')->comment('保底次數');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gachas', function($table) {
            $table->dropColumn('max_times');
        });
    }
};
