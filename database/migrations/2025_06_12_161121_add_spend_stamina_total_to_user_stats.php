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
        Schema::table('user_stats', function (Blueprint $table) {
            if (!Schema::hasColumn('user_stats', 'spend_stamina_total')) {
                $table->integer('spend_stamina_total')->default(0)->after('ugc_clear_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_stats', function (Blueprint $table) {
            if (Schema::hasColumn('user_stats', 'spend_stamina_total')) {
                $table->dropColumn('spend_stamina_total');
            }
        });
    }
};
