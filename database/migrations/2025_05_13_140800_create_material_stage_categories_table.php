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
        Schema::create('material_stage_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('名字');
            $table->string('localization_name')->nullable()->comment('本地化名字');
            $table->integer('parent_id')->default(0)->comment('父層id');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamp('start_time')->nullable()->comment('啟用時間');
            $table->timestamp('end_time')->nullable()->comment('結束時間');
            $table->integer('is_active')->default(1)->comment('是否啟用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_stage_categories');
    }
};
