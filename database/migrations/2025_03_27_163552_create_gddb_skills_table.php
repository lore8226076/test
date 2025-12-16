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
        Schema::create('gddb_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('skill_id');
            $table->string('name');
            $table->string('desc');
            $table->string('ui_sprite_name');
            $table->unsignedInteger('cool_down');
            $table->unsignedInteger('anim_index');
            $table->float('active_delay');
            $table->float('active_timeout');
            $table->float('distance');
            $table->float('protection_time');
            $table->string('hit_reaction');
            $table->string('hit_dir_mode');
            $table->unsignedInteger('knockup_speed_xz');
            $table->unsignedInteger('knockup_speed_y');
            $table->integer('damage');
            $table->boolean('minimum_damage_limit')->default(false); // 要轉回 TRUE / FALSE
            $table->unsignedInteger('minimum_damage_value');
            $table->boolean('proj_attach_on_bone')->default(false); // 要轉回 TRUE / FALSE
            $table->string('proj_attach_bone_name');
            $table->string('proj_prefab');
            $table->unsignedInteger('proj_explosion_damage');
            $table->string('proj_tracking_mode');
            $table->boolean('parabola')->default(false); // 要轉回 TRUE / FALSE
            $table->integer('proj_destory_skill_id');
            $table->integer('proj_destory_npc_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gddb_skills');
    }
};
