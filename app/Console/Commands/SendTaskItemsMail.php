<?php
namespace App\Console\Commands;

use App\Models\UserInboxEntries;
use App\Models\UserTasks;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SendTaskItemsMail extends Command
{
    protected $signature = 'app:send-task-items';

    protected $description = '將有時間限制的信件，在12點前發送信件';

    public function handle()
    {
        //  目前會自動寄信的任務類型 每日, 每周
        $this->sendDailyTaskItemsMail();

        if ($this->isWeeklyTaskTime()) {
            $this->sendWeeklyTaskItemsMail();
        }
    }

    // 每日任務道具發送
    private function sendDailyTaskItemsMail()
    {
        \Log::info('開始發送每日任務道具結算信件');
        $this->sendTaskItemsMailByType(['daily', 'daily_bonus'], 1, 'daily');
    }
    // 每周任務道具發送
    private function sendWeeklyTaskItemsMail()
    {
        \Log::info('開始發送每週任務道具結算信件');
        $this->sendTaskItemsMailByType(['weekly', 'weekly_bonus'], 2, 'weekly');
    }

    // 依類型發送信件
    private function sendTaskItemsMailByType(array $taskTypes, int $inboxMessageId, string $period = 'daily')
    {
        $users = $this->getTaskQuery($taskTypes, [
            'status'        => 'completed',
            'reward_status' => 0,
        ])->get();

        \Log::info("[{$period}] 任務資料筆數：" . $users->count());

        if ($users->isEmpty()) {
            \Log::info("[{$period}] 無符合條件的使用者，不發送信件");
            return;
        }

        DB::beginTransaction();

        try {
            $mergedRewards = collect($users)
                ->flatMap(function ($user) {
                    return collect($user['task']['reward'])
                        ->filter(fn($reward) => ! in_array($reward['item_id'], [201, 202]))
                        ->map(function ($reward) use ($user) {
                            return [
                                'uid'     => $user['uid'],
                                'item_id' => $reward['item_id'],
                                'amount'  => $reward['amount'],
                            ];
                        });
                })
                ->groupBy('uid')
                ->map(function ($items) {
                    return collect($items)
                        ->groupBy('item_id')
                        ->map(function ($group) {
                            return [
                                'item_id' => $group->first()['item_id'],
                                'amount'  => $group->sum('amount'),
                            ];
                        })
                        ->values();
                })
                ->toArray();

            \Log::info("[{$period}] 合併後需發送信件的使用者數量：" . count($mergedRewards));

            foreach ($mergedRewards as $uid => $rewards) {
                \Log::info("[{$period}] 發送信件給使用者 UID: {$uid}，附件數量：" . count($rewards));

                $customAttachments = collect($rewards)->map(fn($r) => [
                    'item_id' => $r['item_id'],
                    'amount'  => $r['amount'],
                ])->values()->toArray();

                $date = $period === 'weekly'
                ? Carbon::now()->startOfWeek()
                : Carbon::now()->startOfDay();

                $existing = UserInboxEntries::where('uid', $uid)
                    ->where('inbox_messages_id', $inboxMessageId)
                    ->whereDate('created_at', $date)
                    ->first();

                if ($existing) {
                    \Log::info("[{$period}] UID: {$uid} 已存在信件，進行附件合併");
                    $original                     = $existing->custom_attachments ?? [];
                    $existing->custom_attachments = array_merge($original, $customAttachments);
                    $existing->attachment_status  = 'unclaimed';
                    $existing->status             = 'unread';
                    $existing->save();
                } else {
                    \Log::info("[{$period}] UID: {$uid} 建立新信件");
                    UserInboxEntries::create([
                        'uid'                => $uid,
                        'inbox_messages_id'  => $inboxMessageId,
                        'status'             => 'unread',
                        'custom_attachments' => $customAttachments,
                        'attachment_status'  => 'unclaimed',
                    ]);
                }
            }

            foreach ($users as $user) {
                if (! empty($user)) {
                    UserTasks::where('id', $user->id)
                        ->update(['reward_status' => 1]);
                }
            }

            DB::commit();
            \Log::info("[{$period}] 任務信件發送完成");
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("[sendTaskItemsMailByType][{$period}] 發送失敗", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    // 取得對應任務的query
    private function getTaskQuery(array $taskTypes, array $extraConditions = [])
    {
        $query = UserTasks::with(['task:id,type,reward,repeatable_type'])
            ->whereHas('task', function ($q) use ($taskTypes) {
                $q->whereIn('type', $taskTypes);
            });

        foreach ($extraConditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query;
    }

    // 新增道具信件
    private function addTaskItemsMail($user, $task)
    {
        $userInboxEntries = UserInboxEntries::whereIn('task_type', $taskTypes)->get();
    }

    // 每週任務結算時間
    private function isWeeklyTaskTime(): bool
    {
        $now = Carbon::now();

        // 只有在「週日 23:59」那一分鐘內才回傳 true
        return $now->dayOfWeek === Carbon::SUNDAY &&
        $now->format('H:i') === '23:59';
    }

}
