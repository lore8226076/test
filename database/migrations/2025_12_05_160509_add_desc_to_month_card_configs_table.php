<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('month_card_configs', function (Blueprint $table) {
            $table->json('desc')->nullable()->after('localization_name')->comment('月卡描述 JSON {desc1, desc2, desc3...}');
        });
    }

    public function down(): void
    {
        Schema::table('month_card_configs', function (Blueprint $table) {
            $table->dropColumn('desc');
        });
    }
};
