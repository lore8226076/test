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
        if (Schema::hasTable('character_deploy_slots')) {
            Schema::dropIfExists('character_deploy_slots');
        }

        Schema::create('character_deploy_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->comment('人物uid');
            $table->integer('character_id')->nullable()->comment('角色ID');
            $table->integer('level')->default(1)->comment('角色等級');
            $table->integer('position')->comment('角色陣位順序');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_deploy_slots');
    }
};
