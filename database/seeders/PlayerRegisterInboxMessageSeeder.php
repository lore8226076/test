<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlayerRegisterInboxMessageSeeder extends Seeder
{
    const REWARD_ITEM_ID = 101; // 星環幣 Item ID

    const REWARD_AMOUNT = 600; //

    const FIRST_DAY_INBOX_ID = 20; // 第一天信件 ID

    const OTHER_DAYS_INBOX_ID = 21; // 其他天信件 ID

    public function run(): void
    {
        $ary1 = [
            'title' => 'IP 串聯大集合！登入就送十日星環幣！',
            'content' => '嘿，各位新晉藝術家們！ \n\n這次開服，我們邀請了多組可愛逗趣的人氣 IP 一同登場， \n\n一起為世界添上色彩。 \n\n自首次登入起，連續十天， \n\n每日都將寄送星環幣到你的信箱。 \n\n跨界角色齊聚、限定主題全面開放—— \n\n多重 IP 正等待你熱情探索！ \n\n希望這份開局好禮，能陪你愉快啟程、盡情創作！ \n\n《鏘鏘鏘藝術派對》營運團隊 \n\n',
            'reward_items' => ['item_id' => self::REWARD_ITEM_ID, 'amount' => self::REWARD_AMOUNT],
        ];
        $ary2 = [
            'title' => '今日星環幣贈禮已送達！',
            'content' => '親愛的藝術家：\n\n今日的星環幣已寄送至信箱，請記得查收！\n\n多重 IP 活動仍在熱鬧進行中，\n\n歡迎你隨時回來走走、探索更多主題造型與合作內容。\n\n祝你創作愉快！\n\n《鏘鏘鏘藝術派對》營運團隊',
            'reward_items' => ['item_id' => self::REWARD_ITEM_ID, 'amount' => self::REWARD_AMOUNT],
        ];
        try {
            $firstDayExists = DB::table('inbox_messages')->where('id', self::FIRST_DAY_INBOX_ID)->exists();
            if (! $firstDayExists) {
                DB::table('inbox_messages')->insert([
                    'id' => self::FIRST_DAY_INBOX_ID,
                    'title' => $ary1['title'],
                    'content' => $ary1['content'],
                    'sender_type' => 'gm',
                    'target_type' => 'batch',
                    'status' => 'active',
                ]);

                DB::table('inbox_attachments')->insert([
                    'inbox_messages_id' => self::FIRST_DAY_INBOX_ID,
                    'item_id' => $ary1['reward_items']['item_id'],
                    'amount' => $ary1['reward_items']['amount'],
                ]);
            } else {
                echo "第一天信件記錄已存在，放棄插入\n";
            }

            $otherDaysExists = DB::table('inbox_messages')->where('id', self::OTHER_DAYS_INBOX_ID)->exists();
            if (! $otherDaysExists) {
                DB::table('inbox_messages')->insert([
                    'id' => self::OTHER_DAYS_INBOX_ID,
                    'title' => $ary2['title'],
                    'content' => $ary2['content'],
                    'sender_type' => 'gm',
                    'target_type' => 'batch',
                    'status' => 'active',
                ]);

                DB::table('inbox_attachments')->insert([
                    'inbox_messages_id' => self::OTHER_DAYS_INBOX_ID,
                    'item_id' => $ary2['reward_items']['item_id'],
                    'amount' => $ary2['reward_items']['amount'],
                ]);
            } else {
                echo "其他天信件記錄已存在，放棄插入\n";
            }
        } catch (\Exception $e) {
            echo '錯誤訊息：'.$e->getMessage();
        }
    }
}
