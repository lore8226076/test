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
        Schema::table('gddb_surgame_heroes', function (Blueprint $table) {
            $table->unsignedBigInteger('character_id')->after('id')->nullable()->comment('角色ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gddb_surgame_heroes', function (Blueprint $table) {
            $table->dropColumn('character_id');
        });
    }
};
