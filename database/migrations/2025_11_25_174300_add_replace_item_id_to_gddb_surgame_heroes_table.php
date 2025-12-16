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
            $table->integer('replace_item_id')->nullable()->after('unique_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gddb_surgame_heroes', function (Blueprint $table) {
            $table->dropColumn('replace_item_id');
        });
    }
};
