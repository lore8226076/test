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
            // A surgame 介面介紹是否完成
            $table->boolean('teaching_surgame_intro')->default(false)->comment('surgame介面介紹是否完成')->after('teaching_gacha');

            // B 是否遊玩過主線關卡2
            $table->boolean('teaching_main_stage2')->default(false)->comment('是否遊玩過主線關卡2')->after('teaching_surgame_intro');

            // C 布陣介面介紹是否完成
            $table->boolean('teaching_formation_intro')->default(false)->comment('布陣介面介紹是否完成')->after('teaching_main_stage2');

            // D 軍階介面介紹是否完成
            $table->boolean('teaching_rank_intro')->default(false)->comment('軍階介面介紹是否完成')->after('teaching_formation_intro');

            // E 是否領取米開找到的券
            $table->boolean('teaching_mikai_ticket')->default(false)->comment('是否領取米開找到的券')->after('teaching_rank_intro');

            // F 是否完成紙娃娃介面教學
            $table->boolean('teaching_paperdoll_intro')->default(false)->comment('紙娃娃介面教學是否完成')->after('teaching_mikai_ticket');

            // G 是否已被引導進入廣場
            $table->boolean('teaching_square_guide')->default(false)->comment('是否已被引導進入廣場')->after('teaching_paperdoll_intro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'teaching_surgame_intro',
                'teaching_main_stage2',
                'teaching_formation_intro',
                'teaching_rank_intro',
                'teaching_mikai_ticket',
                'teaching_paperdoll_intro',
                'teaching_square_guide',
            ]);
        });
    }
};
