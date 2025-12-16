<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inbox_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名稱');
            $table->string('description')->nullable()->comment('描述');
            $table->string('code')->unique()->comment('代碼');
            $table->boolean('active')->default(true)->comment('是否啟用');
            $table->dateTime('start_at')->nullable()->comment('活動開始時間');
            $table->dateTime('end_at')->nullable()->comment('結束日期');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox_categories');
    }
};
