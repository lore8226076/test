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
        Schema::table('user_inbox_entries', function (Blueprint $table) {
            $table->json('custom_attachments')->nullable()->comment('自定義附件');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_inbox_entries', function (Blueprint $table) {
            $table->dropColumn('custom_attachments');
        });
    }
};
