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
            // 移除舊欄位
            $table->dropColumn('summon_1_or_more_pets_count');
            $table->dropColumn('summon_2_or_more_pets_count');
            $table->dropColumn('summon_3_or_more_pets_count');
            $table->dropColumn('summon_pet_1_total_count');
            $table->dropColumn('summon_pet_2_total_count');
            $table->dropColumn('summon_pet_3_total_count');
            $table->dropColumn('summon_pet_4_total_count');
            $table->dropColumn('summon_pet_5_total_count');
            $table->dropColumn('summon_pet_6_total_count');
            $table->dropColumn('summon_same_pet_3_or_more_times');
            // 新增新欄位
            $table->integer('summon_count1')->default(0)->comment('一次叫出 1 隻以上寵物的次數');
            $table->integer('summon_count2')->default(0)->comment('累計一次叫出 2 隻以上寵物的次數');
            $table->integer('summon_count3')->default(0)->comment('累計一次叫出 3 隻以上寵物的次數');
            $table->integer('summon_times_pig')->default(0)->comment('召喚過幾隻寵物 1（一次3隻就+3）');
            $table->integer('summon_times_chameleon')->default(0)->comment('召喚過幾隻寵物 2（一次3隻就+3）');
            $table->integer('summon_times_cow')->default(0)->comment('召喚過幾隻寵物 3（一次3隻就+3）');
            $table->integer('summon_times_rabbit')->default(0)->comment('召喚過幾隻寵物 4（一次3隻就+3）');
            $table->integer('summon_times_pufferfish')->default(0)->comment('召喚過幾隻寵物 5（一次3隻就+3）');
            $table->integer('summon_times_bear')->default(0)->comment('召喚過幾隻寵物 6（一次3隻就+3）');
            $table->integer('summon_count3_samepet')->default(0)->comment('召喚同隻寵物 3 次以上');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_stats', function (Blueprint $table) {
            $table->dropColumn('summon_count1');
            $table->dropColumn('summon_count2');
            $table->dropColumn('summon_count3');
            $table->dropColumn('summon_times_pig');
            $table->dropColumn('summon_times_chameleon');
            $table->dropColumn('summon_times_cow');
            $table->dropColumn('summon_times_rabbit');
            $table->dropColumn('summon_times_pufferfish');
            $table->dropColumn('summon_times_bear');
            $table->dropColumn('summon_count3_samepet');
            $table->integer('summon_1_or_more_pets_count')->default(0)->comment('召喚 1 隻以上寵物次數');
            $table->integer('summon_2_or_more_pets_count')->default(0)->comment('召喚 2 隻以上寵物次數');
            $table->integer('summon_3_or_more_pets_count')->default(0)->comment('召喚 3 隻以上寵物次數');
            $table->integer('summon_pet_1_total_count')->default(0)->comment('召喚寵物1總次數');
            $table->integer('summon_pet_2_total_count')->default(0)->comment('召喚寵物2總次數');
            $table->integer('summon_pet_3_total_count')->default(0)->comment('召喚寵物3總次數');
            $table->integer('summon_pet_4_total_count')->default(0)->comment('召喚寵物4總次數');
            $table->integer('summon_pet_5_total_count')->default(0)->comment('召喚寵物5總次數');
            $table->integer('summon_pet_6_total_count')->default(0)->comment('召喚寵物6總次數');
            $table->integer('summon_same_pet_3_or_more_times')->default(0)->comment('召喚同隻寵物達 3 次以上次數');
        });
    }
};
