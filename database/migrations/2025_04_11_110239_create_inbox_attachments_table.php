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
        Schema::create('inbox_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbox_messages_id')->constrained('inbox_messages')->onDelete('cascade');
            $table->integer('item_id')->nullable()->comment('物品ID');
            $table->integer('amount')->nullable()->comment('數量');
            $table->timestamps();

            $table->index('inbox_messages_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox_attachments');
    }
};
