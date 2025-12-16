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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('teaching_mikai_ticket');
            $table->dropColumn('teaching_square_guide');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('teaching_mikai_ticket')->default(false)->comment('是否領取米開找到的券')->after('teaching_rank_intro');
            $table->boolean('teaching_square_guide')->default(false)->comment('是否已被引導進入廣場')->after('teaching_paperdoll_intro');
        });
    }
};
