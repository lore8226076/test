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
        Schema::table('user_maps', function (Blueprint $table) {
            $table->boolean('has_publish')->default(false)->after('is_home')->comment('是否發布過');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_maps', function (Blueprint $table) {
            $table->dropColumn('has_publish');
        });
    }
};
