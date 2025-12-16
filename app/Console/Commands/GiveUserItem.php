<?php

namespace App\Console\Commands;

use App\Models\UserItemLogs;
use App\Models\Users;
use App\Service\UserItemService;
use Illuminate\Console\Command;

class GiveUserItem extends Command
{
    /**
     * target: UID or 'all'/'全體'
     * item_def: ItemID or 'base'
     */
    protected $signature = 'user:give-item
                            {target : 目標 UID 或 "all"}
                            {item_def : 道具 ID 或 "base"}
                            {qty=1 : 數量}';

    protected $description = '發送道具指令';

    // 定義 Base指令的道具清單
    private function baseItems(): array
    {
        return [
            ['id' => 12, 'qty' => 1],
            ['id' => 13, 'qty' => 1],
            ['id' => 14, 'qty' => 1],
            ['id' => 1020000, 'qty' => 1],
            ['id' => 1020001, 'qty' => 1],
            ['id' => 1020002, 'qty' => 1],
            ['id' => 1020003, 'qty' => 1],
            ['id' => 1020004, 'qty' => 1],
            ['id' => 1020005, 'qty' => 1],
            ['id' => 1020006, 'qty' => 1],
            ['id' => 1020007, 'qty' => 1],
            ['id' => 1020008, 'qty' => 1],
            ['id' => 1020009, 'qty' => 1],
            ['id' => 1020010, 'qty' => 1],
            ['id' => 1020011, 'qty' => 1],
            ['id' => 1020012, 'qty' => 1],
            ['id' => 1020013, 'qty' => 1],
            ['id' => 1030000, 'qty' => 1],
            ['id' => 1030001, 'qty' => 1],
            ['id' => 1030002, 'qty' => 1],
            ['id' => 1030003, 'qty' => 1],
            ['id' => 1030004, 'qty' => 1],
            ['id' => 1030005, 'qty' => 1],
            ['id' => 1030006, 'qty' => 1],
            ['id' => 1030007, 'qty' => 1],
            ['id' => 1030008, 'qty' => 1],
            ['id' => 1030009, 'qty' => 1],
            ['id' => 1030010, 'qty' => 1],
            ['id' => 1030011, 'qty' => 1],
            ['id' => 1030012, 'qty' => 1],
            ['id' => 1030013, 'qty' => 1],
            ['id' => 1010000, 'qty' => 1],
            ['id' => 1010001, 'qty' => 1],
            ['id' => 1010002, 'qty' => 1],
            ['id' => 1010003, 'qty' => 1],
            ['id' => 1010004, 'qty' => 1],
            ['id' => 1010005, 'qty' => 1],
            ['id' => 1010006, 'qty' => 1],
            ['id' => 1010007, 'qty' => 1],
            ['id' => 1010008, 'qty' => 1],
            ['id' => 1010009, 'qty' => 1],
            ['id' => 1010010, 'qty' => 1],
            ['id' => 1010011, 'qty' => 1],
            ['id' => 1010012, 'qty' => 1],
            ['id' => 1010013, 'qty' => 1],
        ];
    }

    public function handle()
    {
        $target = $this->argument('target');
        $itemDef = $this->argument('item_def');
        $qty = $this->argument('qty');

        // 1. 解析目標
        $isAllUsers = in_array($target, ['all', 'all-user', '全體']);

        // 2. 解析道具內容
        $items = [];
        if ($itemDef === 'base') {
            $items = $this->baseItems();
        } else {
            $items = [['id' => $itemDef, 'qty' => $qty]];
        }

        // 3. 輸出確認資訊
        $this->info('目標: '.($isAllUsers ? '全體用戶 (僅補發)' : "用戶 UID: {$target}"));
        $this->table(['道具 ID', '數量'], $items);

        if (! $this->confirm('確認執行？', true)) {
            $this->info('已中止。');

            return 0;
        }

        // 4. 執行
        if ($isAllUsers) {
            foreach ($items as $item) {
                $this->processAllUsers($item['id'], $item['qty']);
            }
        } else {
            $this->processSingleUser($target, $items);
        }

        $this->info('Done.');

        return 0;
    }

    protected function processSingleUser($uid, array $items)
    {
        $user = Users::where('uid', $uid)->first();
        if (! $user) {
            $this->error("找不到用戶: {$uid}");

            return;
        }

        foreach ($items as $item) {
            $this->giveItem($user, $item['id'], $item['qty']);
            $this->line("已發送道具 {$item['id']} 給 {$uid}");
        }
    }

    protected function processAllUsers($itemId, $qty)
    {
        $this->line("處理道具 ID: {$itemId}...");

        // 篩選條件：沒有該道具 OR 該道具數量為 0
        $query = Users::where(function ($q) use ($itemId) {
            $q->whereDoesntHave('userItems', fn ($q2) => $q2->where('item_id', $itemId))
                ->orWhereHas('userItems', fn ($q2) => $q2->where('item_id', $itemId)->where('qty', 0));
        });

        $count = $query->count();
        if ($count === 0) {
            $this->line(' -> 無需補發。');

            return;
        }

        $this->line(" -> 找到 {$count} 位用戶。");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->chunk(1000, function ($users) use ($itemId, $qty, $bar) {
            foreach ($users as $user) {
                $this->giveItem($user, $itemId, $qty);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }

    protected function giveItem($user, $itemId, $qty)
    {
        try {
            UserItemService::addItem(
                UserItemLogs::TYPE_SYSTEM,
                $user->id,
                $user->uid,
                $itemId,
                $qty,
                1,
                '補發道具'
            );
        } catch (\Exception $e) {
            $this->error("發送道具失敗 User: {$user->uid}, Item: {$itemId}, Qty: {$qty}, Error: ".$e->getMessage());
        }
    }
}
