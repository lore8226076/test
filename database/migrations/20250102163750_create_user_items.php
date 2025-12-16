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
        Schema::create('user_items', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->integer('uid')->index();
            $table->integer('item_id');
            $table->string('region')->comment('分辨紙娃娃還是地圖物件')->index();
            $table->string('category');
            $table->string('type');
            $table->integer('qty');
            $table->boolean('is_lock')->default(0);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE user_items ROW_FORMAT = DYNAMIC");
        DB::statement("ALTER TABLE user_items AUTO_INCREMENT = 27");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_items');
    }
};
