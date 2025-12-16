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
        Schema::table('user_item_logs', function (Blueprint $table) {
            $table->integer('manager_id')->default(0)->after('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_item_logs', function (Blueprint $table) {
            $table->dropColumn('manager_id');
        });
    }
};
