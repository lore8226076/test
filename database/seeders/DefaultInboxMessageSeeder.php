<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultInboxMessageSeeder extends Seeder
{
    const DEFAULT_INBOX_MESSAGE_ID = 31;

    const REWARD_ITEM_ID = 172;

    const REWARD_AMOUNT = 10;

    public function run(): void
    {
        $ary1 = [
            'title' => '幸運抽獎券*10',
            'content' => '我們準備了10張抽獎券給您~快去抽獎吧！',
            'reward_items' => ['item_id' => self::REWARD_ITEM_ID, 'amount' => self::REWARD_AMOUNT],
        ];
        try {
            $firstDayExists = DB::table('inbox_messages')->where('id', self::DEFAULT_INBOX_MESSAGE_ID)->exists();
            if (! $firstDayExists) {
                DB::table('inbox_messages')->insert([
                    'id' => self::DEFAULT_INBOX_MESSAGE_ID,
                    'title' => $ary1['title'],
                    'content' => $ary1['content'],
                    'sender_type' => 'gm',
                    'target_type' => 'all',
                    'status' => 'active',
                ]);
            }

            $attachmentExists = DB::table('inbox_attachments')
                ->where('inbox_messages_id', self::DEFAULT_INBOX_MESSAGE_ID)
                ->where('item_id', self::REWARD_ITEM_ID)
                ->exists();
            if (! $attachmentExists) {
                DB::table('inbox_attachments')->insert([
                    'inbox_messages_id' => self::DEFAULT_INBOX_MESSAGE_ID,
                    'item_id' => self::REWARD_ITEM_ID,
                    'amount' => self::REWARD_AMOUNT,
                ]);
            }
        } catch (\Exception $e) {
            echo '插入預設信件記錄失敗: '.$e->getMessage()."\n";
        }

    }
}
