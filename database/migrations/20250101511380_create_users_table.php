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
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id', false, false)->autoIncrement();
            $table->string('uid', 255)->nullable()->collation('utf8mb4_general_ci')->index('uid');
            $table->string('mac_id', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('email', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('account', 255)->collation('utf8mb4_general_ci');
            $table->string('password', 255)->collation('utf8mb4_general_ci');
            $table->string('name', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->tinyInteger('gender')->default(0);
            $table->string('cellphone', 255)->nullable()->collation('utf8mb4_general_ci')->comment('聯絡電話');
            $table->integer('map_limit')->default(3);
            $table->text('introduce')->nullable()->collation('utf8mb4_general_ci');
            $table->integer('is_active')->default(1)->comment('啟(停)用 1 (0)');
            $table->string('firebase_name', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('firebase_uid', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->string('firebase_provider_id', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->text('firebase_access_token')->nullable()->collation('utf8mb4_general_ci');
            $table->string('firebase_photo_url', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->integer('sort')->default(0);
            $table->longText('remember_token')->nullable()->collation('utf8mb4_general_ci');
            $table->string('updated_name', 255)->nullable()->collation('utf8mb4_general_ci');
            $table->dateTime('created_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
        });

        // 設定資料表選項
        DB::statement("ALTER TABLE users COMMENT = '玩家帳號'");
        DB::statement("ALTER TABLE users ENGINE = InnoDB");
        DB::statement("ALTER TABLE users COLLATE = 'utf8mb4_general_ci'");
        DB::statement("ALTER TABLE users ROW_FORMAT = DYNAMIC");
        DB::statement("ALTER TABLE users AUTO_INCREMENT = 763");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
