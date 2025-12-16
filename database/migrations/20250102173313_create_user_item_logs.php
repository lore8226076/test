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
        Schema::create('user_item_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('user_id')->index();
            $table->integer('user_item_id')->index();
            $table->integer('item_id')->index();
            $table->integer('user_pay_order_id')->nullable();
            $table->integer('user_mall_order_id')->nullable();
            $table->integer('original_qty');
            $table->integer('qty');
            $table->integer('after_qty');
            $table->string('memo');
            $table->string('update_name')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE user_item_logs ROW_FORMAT = DYNAMIC");
        DB::statement("ALTER TABLE user_item_logs AUTO_INCREMENT = 25");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_item_logs');
    }
};
