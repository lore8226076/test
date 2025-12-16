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
        Schema::create('user_surgame_resource_stage_records', function (Blueprint $table) {
            $table->id();
            $table->integer('uid');
            $table->integer('stage_unique_id');
            $table->string('type');
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();
            $table->unique(['uid', 'stage_unique_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_surgame_resource_stage_records');
    }
};
