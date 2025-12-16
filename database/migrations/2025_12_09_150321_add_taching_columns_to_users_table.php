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
        Schema::table('users', function (Blueprint $table) {
            // 是否領過時裝券 & 是否抽過時裝抽 以teaching_開頭
            $table->boolean('teaching_received_costume_coupon')->default(false)->comment('是否領過時裝券');
            $table->boolean('teaching_performed_costume_gacha')->default(false)->comment('是否抽過時裝抽');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('teaching_received_costume_coupon');
            $table->dropColumn('teaching_performed_costume_gacha');
        });
    }
};
