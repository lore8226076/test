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
        /** 只有Mail的類型是single或batch時，才會有MailTarget */
        Schema::create('inbox_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbox_messages_id')->constrained('inbox_messages')->onDelete('cascade');
            $table->string('target_uid')->comment('接收者UID');
            $table->timestamps();

            $table->index('inbox_messages_id');
            $table->index('target_uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox_targets');
    }
};
