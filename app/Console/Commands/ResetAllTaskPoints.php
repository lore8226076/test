<?php
namespace App\Console\Commands;

use App\Models\UserItemLogs;
use App\Models\UserItems;
use App\Models\Users;
use App\Service\UserItemService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetAllTaskPoints extends Command
{
    protected $signature   = 'points:reset-all {--force} {--uid=}';
    protected $description = '每天清除每日積分，週一額外清除每週積分 (item_id 201, 202)';

    public function handle()
    {
        $uid   = $this->option('uid');
        $force = (bool) $this->option('force');

        Log::info('【任務】開始清除積分', ['uid' => $uid, 'force' => $force]);
        $this->info('開始清除積分...');

        if ($uid) {
            $user = Users::where('uid', $uid)->first();
            if (! $user) {
                $this->error("找不到使用者 UID: {$uid}");
                Log::warning('【任務】找不到使用者', ['uid' => $uid]);
                return self::FAILURE;
            }

            $this->resetDailyPoints($user);

            if ($force) {
                $this->info("強制清除使用者 {$uid} 的每週積分...");
                Log::info('【任務】針對使用者清除每週積分', ['uid' => $uid]);
                $this->resetWeeklyPoints($user);
            }
        } else {
            $this->resetDailyPoints();

            if (now()->isMonday()) {
                $this->info('自動清除所有人的每週積分（週一）...');
                Log::info('【任務】自動清除全體週積分（週一）');
                $this->resetWeeklyPoints();
            }
        }

        $this->info('積分清除完成。');
        Log::info('【任務】積分清除完成', ['uid' => $uid]);
        return self::SUCCESS;
    }

    private function resetDailyPoints($user = null)
    {
        $this->info('清除每日積分...');
        Log::info('【每日積分】開始清除');

        if ($user) {
            $items = UserItems::where('user_id', $user->id)->where('item_id', 201)->where('qty', '>', 0)->get();
        } else {
            $items = UserItems::where('item_id', 201)->where('qty', '>', 0)->get();
        }

        $users = $user
        ? collect([$user])->keyBy('id')
        : Users::select('id', 'uid')->whereIn('id', $items->pluck('user_id')->toArray())->get()->keyBy('id');

        $count = 0;

        foreach ($items as $entry) {
            $targetUser = $users->get($entry->user_id);
            if (! $targetUser) {
                continue;
            }

            Log::debug('【每日積分】準備清除', [
                'uid'     => $targetUser->uid,
                'user_id' => $entry->user_id,
                'qty'     => $entry->qty,
            ]);

            $result = UserItemService::removeItem(
                UserItemLogs::TYPE_SYSTEM,
                $entry->user_id,
                $targetUser->uid,
                201,
                $entry->qty,
                1,
                '每日積分清零'
            );

            if ($result['success']) {
                $count++;
            }

        }

        $this->info("成功清除 {$count} 位玩家的每日積分。");
        Log::info("【每日積分】共成功清除 {$count} 位玩家的積分");
    }

    private function resetWeeklyPoints($user = null)
    {
        $this->info('清除每週積分...');
        Log::info('【每週積分】開始清除', ['uid' => $user?->uid]);

        $targetUsers = $user
        ? collect([$user])
        : Users::select('id', 'uid')->get();

        $count = 0;

        foreach ($targetUsers as $targetUser) {
            $totalQty = UserItems::where('user_id', $targetUser->id)
                ->where('item_id', 202)
                ->value('qty');

            if ($totalQty <= 0) {
                continue;
            }

            Log::debug('【每週積分】準備清除', [
                'uid'     => $targetUser->uid,
                'user_id' => $targetUser->id,
                'qty'     => $totalQty,
            ]);

            $result = UserItemService::removeItem(
                UserItemLogs::TYPE_SYSTEM,
                $targetUser->id,
                $targetUser->uid,
                202,
                $totalQty,
                1,
                '每週積分清零'
            );

            if ($result['success']) {
                $count++;
            } else {
                Log::error('【每週積分】清除失敗', [
                    'uid'      => $targetUser->uid,
                    'user_id'  => $targetUser->id,
                    'response' => $result,
                ]);
            }
        }

        $this->info("成功清除 {$count} 位玩家的每週積分。");
        Log::info("【每週積分】共成功清除 {$count} 位玩家的積分");
    }

}
