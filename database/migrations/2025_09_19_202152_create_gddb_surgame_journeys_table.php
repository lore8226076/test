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
          if (Schema::dropIfExists('gddb_surgame_journeys')) {
            $table->drop('gddb_surgame_journeys');
        }
        Schema::create('gddb_surgame_journeys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unique_id')->unique();
            $table->string('name')->nullable()->comment('章節名稱');
            $table->integer('stage_id')->nullable()->comment('關卡ID');
            $table->integer('over_power')->nullable()->comment('通關戰力, 星級挑戰用');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_surgame_journeys');
    }
};
