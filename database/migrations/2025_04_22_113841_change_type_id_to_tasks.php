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
        Schema::table('tasks', function (Blueprint $table) {
            // 移除type_id
            $table->dropColumn('type_id');

            // 新增type
            $table->string('type')->before('description')->nullable();
            $table->integer('category_id')->nullable()->after('type');
            $table->boolean('auto_assign')->default(false)->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // 新增type_id
            $table->string('type_id')->nullable();

            // 移除type
            $table->dropColumn('type');
            $table->dropColumn('category_id');
            $table->dropColumn('auto_assign');
        });
    }
};
