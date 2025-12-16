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
        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->integer('id', false, false)->autoIncrement();                             
            $table->string('name', 255)->nullable()->collation('utf8mb4_general_ci');         
            $table->longText('admin_menu_ids')->nullable()->collation('utf8mb4_general_ci');  
            $table->string('updated_name', 255)->nullable()->collation('utf8mb4_general_ci'); 
            $table->dateTime('created_at')->nullable(false);                                  
            $table->dateTime('updated_at')->nullable(false);                                  
        });

        // 設定表格屬性
        DB::statement("ALTER TABLE admin_permissions COLLATE='utf8mb4_general_ci'");
        DB::statement("ALTER TABLE admin_permissions ENGINE=InnoDB");
        DB::statement("ALTER TABLE admin_permissions ROW_FORMAT=DYNAMIC");
        DB::statement("ALTER TABLE admin_permissions AUTO_INCREMENT=4");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_permissions');
    }
};
