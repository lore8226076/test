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
        Schema::table('item_groups', function (Blueprint $table) {
            // 刪除 items 欄位
            $table->dropColumn('items');
            $table->dropColumn('localzation_name');
            $table->dropColumn('name');
            // 新增 items 欄位
            $table->unsignedBigInteger('parent_item_id')->nullable()->after('id')->index()->comment('gddata_item_id');
            $table->unsignedBigInteger('item_id')->nullable()->after('parent_item_id')->index()->comment('道具id');
            $table->integer('qty')->nullable()->after('item_id')->comment('道具數量');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_groups', function (Blueprint $table) {
            $table->dropColumn('parent_item_id');
            $table->dropColumn('item_id');
            $table->dropColumn('qty');
            $table->json('items')->nullable()->after('id');
            $table->string('localzation_name')->nullable()->after('items');
            $table->string('name')->nullable()->after('localzation_name');
        });
    }
};
