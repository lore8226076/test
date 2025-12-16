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
        Schema::create('user_pets', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->comment('玩家UID')->index();
            $table->string('pet_id')->comment('寵物ID');
            $table->string('pet_name')->comment('寵物名稱');
            $table->string('pet_str')->default(0)->comment('寵物攻擊');
            $table->string('pet_def')->default(0)->comment('寵物防禦');
            $table->string('pet_sta')->default(0)->comment('寵物體質');
            $table->string('pet_exp')->default(0)->comment('寵物經驗');
            $table->string('pet_level')->default(0)->comment('寵物等級');
            $table->string('pet_unallocated_points')->default(0)->comment('寵物未分配點數');
            $table->string('pet_skin_id')->default(0)->comment('寵物皮膚ID');
            $table->softDeletes()->comment('刪除時間');
            $table->timestamps();

            // uid+pet_id
            $table->unique(['uid', 'pet_id'], 'uid_pet_id_unique')->comment('玩家UID+寵物ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pets');
    }
};
