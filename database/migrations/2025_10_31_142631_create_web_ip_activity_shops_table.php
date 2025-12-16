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
        Schema::create('web_ip_activity_shops', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('IP名稱');
            $table->text('description')->nullable()->comment('IP描述');
            $table->text('detailed_description')->nullable()->comment('IP詳細描述');
            $table->integer('votes')->default(0)->comment('投票數');
            $table->string('image_url')->nullable()->comment('IP圖片URL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_ip_activity_shops');
    }
};
