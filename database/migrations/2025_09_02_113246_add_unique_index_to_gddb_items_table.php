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
        Schema::table('gddb_items', function (Blueprint $table) {
            $table->unique(['region', 'category', 'item_id'], 'unique_region_category_item_id');
            $table->unique(['region', 'type', 'item_id'], 'unique_region_type_item_id');
            $table->index(['region', 'category'], 'unique_region_category');
            $table->index(['region', 'type'], 'unique_region_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gddb_items', function (Blueprint $table) {
            $table->dropUnique('unique_region_category_item_id');
            $table->dropUnique('unique_region_type_item_id');
            $table->dropIndex('unique_region_category');
            $table->dropIndex('unique_region_type');
        });
    }
};
