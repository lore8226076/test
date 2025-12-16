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
        Schema::create('gddb_localization_names', function (Blueprint $table) {
            $table->id();
            $table->string('key')->comment('資料key')->index();
            $table->longText('en_info')->nullable()->comment('英文資料');
            $table->longText('zh_info')->nullable()->comment('中文資料');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_localization_names');
    }
};
