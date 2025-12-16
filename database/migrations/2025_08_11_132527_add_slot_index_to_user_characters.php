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
        Schema::table('user_characters', function (Blueprint $table) {
            $table->string('slot_index')->nullable()->after('has_use')->comment('角色索引位置');
            $table->index('uid', 'slot_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_characters', function (Blueprint $table) {
            $table->dropColumn('slot_index');
        });
    }
};
