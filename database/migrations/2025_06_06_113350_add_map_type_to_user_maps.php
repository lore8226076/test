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
            $table->string('map_type')->default(1)->after('player_num')->comment('地圖類型，1:闖關, 2:競速, 3:生存');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_maps', function (Blueprint $table) {
            $table->dropColumn('map_type');
        });
    }
};
