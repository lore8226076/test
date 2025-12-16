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
        Schema::create('item_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id');
            $table->integer('currency_item_id');
            $table->double('price');
            $table->timestamps();
        });

        // 設定表格屬性
        DB::statement("ALTER TABLE item_prices COLLATE='utf8mb4_unicode_ci'");
        DB::statement("ALTER TABLE item_prices ENGINE=InnoDB");
        DB::statement("ALTER TABLE item_prices ROW_FORMAT=DYNAMIC");
        DB::statement("ALTER TABLE item_prices AUTO_INCREMENT=3");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_prices');
    }
};
