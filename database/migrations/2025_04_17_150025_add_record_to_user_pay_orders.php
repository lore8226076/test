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
        Schema::table('user_pay_orders', function (Blueprint $table) {
            $table->timestamp('purchase_time')->after('currency')->nullable()->comment('Google 傳回的購買時間');
            $table->timestamp('acknowledged_at')->after('purchase_time')->nullable()->comment('acknowledge 呼叫時間');
            $table->json('raw_response')->after('acknowledged_at')->nullable()->comment('Google API 回傳原始 JSON');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_pay_orders', function (Blueprint $table) {
            $table->dropColumn('purchase_time');
            $table->dropColumn('acknowledged_at');
            $table->dropColumn('raw_response');
        });
    }
};
