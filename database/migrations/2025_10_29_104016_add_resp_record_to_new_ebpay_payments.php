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
        Schema::table('new_ebpay_payments', function (Blueprint $table) {
            $table->longText('raw_trade_info')->nullable()->after('raw_response')->comment('藍新回傳的 TradeInfo 內容');
            $table->longText('decoded_payload')->nullable()->after('raw_trade_info')->comment('解密後的 TradeInfo 內容');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_ebpay_payments', function (Blueprint $table) {
            $table->dropColumn('raw_trade_info');
            $table->dropColumn('decoded_payload');
        });
    }
};
