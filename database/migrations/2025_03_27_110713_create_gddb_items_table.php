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
        Schema::create('gddb_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->unique(); // 必填
            $table->string('localization_name')->default('XX');
            $table->string('localization_description')->default('0');
            $table->string('category')->default('');
            $table->string('type');
            $table->string('style', 10)->default('None');
            $table->unsignedInteger('price')->default(0);
            $table->boolean('exchangable')->default(false);
            $table->unsignedBigInteger('manager_id')->default(0);
            $table->boolean('network')->default(false);
            $table->integer('npc_id')->default(-1);
            $table->integer('sort_weight')->default(0);
            $table->boolean('show')->default(true);
            $table->string('subtype')->default('None');
            $table->boolean('auto_gen')->default(false);
            $table->string('region')->default('0');
            $table->string('rarity', 10)->default('N');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_items');
    }
};
