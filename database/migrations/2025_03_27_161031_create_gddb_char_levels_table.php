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
        Schema::create('gddb_char_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('lv');
            $table->integer('exp');
            $table->integer('hp');
            $table->integer('bp');
            $table->integer('sp');
            $table->integer('atk');
            $table->integer('def');
            $table->integer('brk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_char_levels');
    }
};
