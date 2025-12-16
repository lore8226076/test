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
        Schema::create('item_price_upload_details', function (Blueprint $table) {
            $table->integer('id', false, false)->autoIncrement();
            $table->integer('item_price_upload_id');
            $table->integer('item_id')->default(0);
            $table->string('tag', 255)->collation('utf8mb4_general_ci');
            $table->integer('currency_item_id')->default(0);
            $table->double('price')->default(0);
            $table->dateTime('created_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
        });

        // 設定表格屬性
        DB::statement("ALTER TABLE settings COLLATE='utf8mb4_general_ci'");
        DB::statement("ALTER TABLE settings ENGINE=InnoDB");
        DB::statement("ALTER TABLE settings ROW_FORMAT=DYNAMIC");
        DB::statement("ALTER TABLE settings AUTO_INCREMENT=1");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_price_upload_details');
    }
};
