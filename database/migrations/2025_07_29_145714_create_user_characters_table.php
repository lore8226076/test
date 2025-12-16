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
        Schema::create('user_characters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid');          // 使用者ID
            $table->unsignedBigInteger('character_id'); // 角色ID
            $table->unsignedInteger('star_level')->default(0); // 角色星級
            $table->boolean('has_use')->default(false)->comment('是否已出戰或使用中');
            $table->timestamps();

            $table->unique(['uid', 'character_id']); // 避免同一角色重複擁有
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_lists');
    }
};
