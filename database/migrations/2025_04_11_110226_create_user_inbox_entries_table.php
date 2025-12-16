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
        Schema::create('user_inbox_entries', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->comment('UID');
            $table->foreignId('inbox_messages_id')->constrained('inbox_messages')->onDelete('cascade');
            $table->enum('status', ['unread', 'read', 'deleted'])->default('unread')->comment('狀態');
            $table->enum('attachment_status', ['unclaimed', 'claimed'])->nullable()->default(null)->comment('附件狀態(null:沒有附件)');
            $table->softDeletes();
            $table->timestamps();
            $table->index('uid');
            $table->index('inbox_messages_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_inbox_entries');
    }
};
