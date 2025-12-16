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
        Schema::table('character_deploy_slots', function (Blueprint $table) {
            $table->unique(['uid', 'position'], 'character_deploy_slots_uid_position_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_deploy_slots', function (Blueprint $table) {
            $table->dropUnique(['character_deploy_slots_uid_position_unique']);
        });
    }
};
