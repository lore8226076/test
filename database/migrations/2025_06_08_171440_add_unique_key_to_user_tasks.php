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
        Schema::table('user_tasks', function (Blueprint $table) {
            $table->unique(['uid', 'task_id', 'created_at'], 'uniq_uid_task_created');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->string('en_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_tasks', function (Blueprint $table) {
            $table->dropUnique('uniq_uid_task_created');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('en_value');
        });
    }
};
