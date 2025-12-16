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
        Schema::create('settings', function (Blueprint $table) {
            $table->integer('id', false, false)->autoIncrement();                             
            $table->string('name', 255)->nullable()->collation('utf8mb4_general_ci');         
            $table->longText('value')->nullable()->collation('utf8mb4_general_ci');           
            $table->string('updated_name', 255)->nullable()->collation('utf8mb4_general_ci'); 
            $table->dateTime('created_at')->nullable(false);                                  
            $table->dateTime('updated_at')->nullable(false);                                  
        });

        // 設定表格屬性
        DB::statement("ALTER TABLE settings COLLATE='utf8mb4_general_ci'");
        DB::statement("ALTER TABLE settings ENGINE=InnoDB");
        DB::statement("ALTER TABLE settings ROW_FORMAT=DYNAMIC");
        DB::statement("ALTER TABLE settings AUTO_INCREMENT=2");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
