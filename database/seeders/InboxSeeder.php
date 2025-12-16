<?php
namespace Database\Seeders;

use App\Models\InboxAttachments;
use App\Models\InboxMessages;
use App\Models\InboxTargets;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InboxSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncateTables();

        $uidList = ['1739356921']; // 測試用玩家 UID

        $hasNullStartAt = false;
        // 建立 10 封信件（全服/單人/批次混合）
        for ($i = 1; $i <= 10; $i++) {
            // 依照順序決定信件類型
            $type = match ($i % 3) {
                0 => 'all',
                1 => 'single',
                default => 'batch',
            };

            if ($type === 'all' && ! $hasNullStartAt) {
                $hasNullStartAt   = true;
                $base['start_at'] = null;
                $base['end_at']   = null;
            } else {
                $base['start_at'] = now()->subDays(rand(0, 2));
                $base['end_at']   = now()->addDays(rand(5, 15));
            }

            // 建立信件主體
            $message = InboxMessages::create([
                'target_type' => $type,
                'status'      => 'active',
                'title'       => "測試信件 #$i",
                'content'     => "這是第 $i 封測試信件內容。",
                'expire_at'   => now()->addDays(rand(3, 30)),
                'start_at'    => $base['start_at'],
                'end_at'      => $base['end_at'],
            ]);

            //  偶數信件加入附件（獎勵）
            if ($i % 2 === 0) {
                InboxAttachments::create([
                    'inbox_messages_id' => $message->id,
                    'item_id'           => 101,
                    'amount'            => 1500,
                ]);

                if ($i % 6 === 0) {
                    InboxAttachments::create([
                        'inbox_messages_id' => $message->id,
                        'item_id'           => 102,
                        'amount'            => 1500,
                    ]);
                }
            }

            // 單人 / 批次信件指定目標玩家
            if (in_array($type, ['single', 'batch'])) {
                $targets = [];

                if ($type === 'single') {
                    $targets = [$uidList[0]]; // 單人就指定這一位
                } else {
                    // 批次信件模擬多筆（重複指定同一人避免錯誤）
                    $count   = rand(2, 4);
                    $targets = array_fill(0, $count, $uidList[0]);
                }

                foreach ($targets as $uid) {
                    InboxTargets::create([
                        'inbox_messages_id' => $message->id,
                        'target_uid'        => $uid,
                    ]);
                }
            }
        }

        for ($i = 11; $i <= 15; $i++) {
            $case = $i - 10;

            $base = [
                'title'       => "無效信件 #$case",
                'content'     => "這是第 $case 封不會出現在收件匣的信件。",
                'target_type' => 'all',
                'status'      => 'active',
                'expire_at'   => now()->addDays(7),
                'start_at'    => now()->subDays(1),
                'end_at'      => now()->addDays(5),
            ];

            switch ($case) {
                case 1:
                    $base['status'] = 'cancelled';
                    break;
                case 2:
                    $base['status'] = 'expired';
                    break;
                case 3:
                    $base['start_at'] = now()->addDays(3);
                    break;
                case 4:
                    $base['end_at'] = now()->subDays(1);
                    break;
                case 5:
                    $base['target_type'] = 'batch';

            }

            $message = InboxMessages::create($base);

            // 第 5 種（batch 沒包含這位玩家）
            if ($case === 5) {
                InboxTargets::create([
                    'inbox_messages_id' => $message->id,
                    'target_uid'        => 'not_the_player', // 無效 UID
                ]);
            }
        }
    }

    private function truncateTables()
    {
        // 清除所有信件
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('inbox_attachments')->truncate();
        DB::table('inbox_targets')->truncate();
        DB::table('user_inbox_entries')->truncate();
        DB::table('inbox_messages')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
