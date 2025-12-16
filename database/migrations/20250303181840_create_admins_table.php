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
        Schema::create('admins', function (Blueprint $table) {
            $table->integer('id', false, false)->autoIncrement();
            $table->string('account', 255)->collation('utf8mb4_general_ci');
            $table->string('password', 255)->collation('utf8mb4_general_ci');
            $table->string('name', 255)->collation('utf8mb4_general_ci');
            $table->string('cellphone', 255)->nullable()->collation('utf8mb4_general_ci')->comment('聯絡電話');
            $table->string('dept', 255)->nullable()->collation('utf8mb4_general_ci')->comment('部門名稱');
            $table->string('title', 255)->nullable()->collation('utf8mb4_general_ci')->comment('職務名稱');
            $table->integer('is_active')->comment('啟(停)用 1 (0)');
            $table->integer('is_adm')->comment('網站管理者:1');
            $table->integer('admin_permission_id')->nullable();
            $table->longText('remember_token')->nullable()->collation('utf8mb4_general_ci');
            $table->dateTime('created_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
        });

        // 設定表格屬性
        DB::statement("ALTER TABLE admins COMMENT='使用者帳號'");
        DB::statement("ALTER TABLE admins COLLATE='utf8mb4_general_ci'");
        DB::statement("ALTER TABLE admins ENGINE=InnoDB");
        DB::statement("ALTER TABLE admins ROW_FORMAT=DYNAMIC");
        DB::statement("ALTER TABLE admins AUTO_INCREMENT=5");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
