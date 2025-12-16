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
        Schema::create('admin_menus', function (Blueprint $table) {
            $table->integer('id', false, false);
            $table->integer('up_id', false, false)->comment('上層編號id');
            $table->integer('sort', false, false)->comment('排序編號');
            $table->string('name', 20)->nullable()->collation('utf8mb4_general_ci')->comment('顯示名稱');
            $table->string('link', 100)->nullable()->collation('utf8mb4_general_ci')->comment('route');
            $table->string('icon', 50)->nullable()->collation('utf8mb4_general_ci')->comment('class');
            $table->integer('blank', false, false)->comment('1:另開視窗');
            $table->string('description', 255)->nullable()->collation('utf8mb4_general_ci')->comment('描述');
            $table->integer('is_dashboard', false, false)->comment('1:不需sub');
            $table->integer('is_active', false, false)->comment('0=停用');

            $table->primary('id');
        });

        // 設定表格屬性
        DB::statement("ALTER TABLE admin_menus COMMENT='權限控管'");
        DB::statement("ALTER TABLE admin_menus COLLATE='utf8mb4_general_ci'");
        DB::statement("ALTER TABLE admin_menus ENGINE=InnoDB");
        DB::statement("ALTER TABLE admin_menus ROW_FORMAT=DYNAMIC");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_menus');
    }
};
