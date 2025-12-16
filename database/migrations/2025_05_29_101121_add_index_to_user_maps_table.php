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
            if (Schema::hasIndex('user_maps', 'idx_user_draft_home')) {
                $table->dropIndex('idx_user_draft_home');
            }
            $table->index(
                ['user_id', 'is_publish', 'is_draft', 'is_home', 'is_deleted'],
                'idx_user_draft_home'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_maps', function (Blueprint $table) {
            $table->dropIndex('idx_user_draft_home');
        });
    }
};
