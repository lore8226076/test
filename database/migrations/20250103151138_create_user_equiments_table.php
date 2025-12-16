<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_equipments', function (Blueprint $table) {
            $table->integer('id', false, false)->autoIncrement();
            $table->integer('user_id', false, false);
            $table->unsignedInteger('basemodel_id')->default(0);
            $table->unsignedInteger('hairstyle_id')->default(0);
            $table->integer('face_id', false, false)->default(0);
            $table->integer('skin_id', false, false)->default(0);
            $table->unsignedInteger('upperbody_id')->default(0);
            $table->unsignedInteger('lowerbody_id')->default(0);
            $table->unsignedInteger('handwear_id')->default(0);
            $table->unsignedInteger('footwear_id')->default(0);
            $table->integer('fx_foot_id', false, false)->default(-1);
            $table->integer('acc_head_id', false, false)->default(-1);
            $table->integer('acc_earings_id', false, false)->default(-1);
            $table->integer('acc_face_id', false, false)->default(-1);
            $table->integer('acc_back_id', false, false)->default(-1);
            $table->integer('weapon_id', false, false)->default(0);
            $table->string('color_index', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('ava_colors', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('head_set', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('back_set', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('updated_name', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->dateTime('created_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
        });

        DB::statement("ALTER TABLE user_equipments COLLATE='utf8mb4_general_ci'");
        DB::statement("ALTER TABLE user_equipments ENGINE=InnoDB");
        DB::statement("ALTER TABLE user_equipments ROW_FORMAT=DYNAMIC");
        DB::statement("ALTER TABLE user_equipments AUTO_INCREMENT=749");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_equipments');
    }
};
