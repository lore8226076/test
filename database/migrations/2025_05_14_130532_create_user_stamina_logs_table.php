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
        Schema::create('user_stamina_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('uid')->comment('玩家ID')->index();
            $table->integer('change_stamina')->comment('體力變更');
            $table->integer('before_stamina')->comment('體力變更前');
            $table->integer('after_stamina')->comment('體力變更後');
            $table->integer('stage_id')->nullable()->comment('關卡ID');
            $table->string('type')->comment('類型:auto(自動),manual(手動),purchase(購買),system(系統)');
            $table->string('remark')->comment('備註');
            $table->timestamp('next_recover_at')->nullable()->comment('type = auto時記錄下次回復時間');
            $table->timestamps();
        });

        // table comment
        DB::statement("ALTER TABLE `user_stamina_logs` COMMENT='玩家體力紀錄表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stamina_logs');
    }
};
