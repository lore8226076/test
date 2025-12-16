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
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->string('follower_uid', 255)->index()->comment('追蹤者uid');
            $table->string('following_uid', 255)->index()->comment('被追蹤者uid');

            $table->timestamps();

            $table->unique(['follower_uid', 'following_uid']);

        });
        DB::statement('ALTER TABLE follows ADD CONSTRAINT check_follower_following_different CHECK (follower_uid != following_uid)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
