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
        Schema::create('map_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name')->comment('標籤名稱');
            $table->string('localize_name')->comment('標籤名稱(多語系)');
            $table->string('sort')->comment('標籤排序');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_tags');
    }
};
