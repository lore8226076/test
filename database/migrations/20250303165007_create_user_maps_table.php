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
        Schema::create('user_maps', function (Blueprint $table) {
            $table->integer('id', false, false)->autoIncrement(); // Laravel 會自動設為 PRIMARY KEY
            $table->integer('user_id', false, false);
            $table->string('map_name', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('map_file_path', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('map_file_name', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->tinyInteger('is_home', false, false)->default(0);
            $table->tinyInteger('is_publish', false, false)->default(0);
            $table->dateTime('publish_at')->nullable();
            $table->text('introduce')->nullable()->collation('utf8mb4_general_ci');
            $table->string('photo_file_path', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('updated_name', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->dateTime('created_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
        });

        // 設定表格屬性
        DB::statement("ALTER TABLE user_maps COLLATE='utf8mb4_general_ci'");
        DB::statement("ALTER TABLE user_maps ENGINE=InnoDB");
        DB::statement("ALTER TABLE user_maps ROW_FORMAT=DYNAMIC");
        DB::statement("ALTER TABLE user_maps AUTO_INCREMENT=107");

        // 設定索引
        DB::statement("ALTER TABLE user_maps ADD INDEX `user_id` (`user_id`) USING BTREE");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_maps');
    }
};
