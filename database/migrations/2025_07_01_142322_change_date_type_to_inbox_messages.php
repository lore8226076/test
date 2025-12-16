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
        Schema::table('inbox_messages', function (Blueprint $table) {
            // start_at, end_at, expire_at 改為 date (yyyy-mm-dd)
            $table->date('start_at')->nullable()->change();
            $table->date('end_at')->nullable()->change();
            $table->date('expire_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inbox_messages', function (Blueprint $table) {
            // start_at, end_at, expire_at 改為 datetime (yyyy-mm-dd hh:mm:ss)
            $table->datetime('start_at')->nullable()->change();
            $table->datetime('end_at')->nullable()->change();
            $table->datetime('expire_at')->nullable()->change();
        });
    }
};
