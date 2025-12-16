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
        Schema::create('material_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('名字');
            $table->integer('category_id')->default(0)->comment('分類id');
            $table->integer('map_id')->nullable()->comment('地圖id');
            $table->string('localization_name')->nullable()->comment('本地化名字');
            $table->string('description')->nullable()->comment('描述');
            $table->integer('difficulty')->default(0)->comment('難度');
            $table->integer('stamina_cost')->default(0)->comment('扣除體力');
            $table->json('reward_items')->nullable()->comment('獎勵物品');
            $table->json('reward_items_rate')->nullable()->comment('獎勵物品機率');
            $table->integer('sort')->default(0)->comment('排序');
            $table->integer('is_active')->default(1)->comment('是否啟用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_stages');
    }
};
