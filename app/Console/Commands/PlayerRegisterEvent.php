<?php

namespace App\Console\Commands;

use App\Models\InboxTargets;
use App\Models\UserInboxEntries;
use App\Models\Users;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlayerRegisterEvent extends Command
{
    protected $signature = 'app:player-register-event {--test-uid= : 指定測試用的 UID}';

    protected $description = '從玩家收到歡迎信(ID=20)的隔天開始，連續發送9天的鑽石獎勵信(ID=21)';

    const WELCOME_INBOX_ID = 20;        // 歡迎信 ID
    const REGISTER_EVENT_INBOX_ID = 21; // 獎勵信 ID
    const MAX_INBOX_COUNT = 9;          // 最多發送 9 封

    public function handle()
    {
        $testUid = $this->option('test-uid');

        // 取得所有收過歡迎信(ID=20)的玩家
        $query = UserInboxEntries::where('inbox_messages_id', self::WELCOME_INBOX_ID)
            ->with('user')
            ->when($testUid, function ($query) use ($testUid) {
                return $query->where('uid', $testUid);
            });

        $welcomeMailRecords = $query->get();

        if ($welcomeMailRecords->isEmpty()) {
            $this->info('沒有符合條件的玩家');
            return 0;
        }

        $processedCount = 0;

        foreach ($welcomeMailRecords as $welcomeMail) {
            $user = $welcomeMail->user;
            if (!$user) {
                continue;
            }

            // 計算從收到歡迎信後過了幾天（隔天開始算第1天）
            $welcomeMailCreatedAt = Carbon::parse($welcomeMail->created_at);
            $daysSinceWelcome = $welcomeMailCreatedAt->diffInDays(Carbon::now());

            // 如果還沒過完第一天（隔天才開始發），跳過
            if ($daysSinceWelcome < 1) {
                $this->info("UID {$user->uid}: 歡迎信建立日期為今天，隔天才開始發送獎勵");
                continue;
            }

            // 如果已經超過 9 天，跳過
            if ($daysSinceWelcome > 9) {
                $this->info("UID {$user->uid}: 已超過9天獎勵期限，跳過");
                continue;
            }

            // 檢查已發送的獎勵信數量
            $currentInboxCount = $this->checkInboxCount($user);

            // 如果已經發滿 9 封，跳過
            if ($currentInboxCount >= self::MAX_INBOX_COUNT) {
                $this->info("UID {$user->uid}: 已達到信件上限(9封)，跳過");
                continue;
            }

            // 計算應該發送的信件數量（daysSinceWelcome 天，但最多 9 封）
            $shouldHaveCount = min($daysSinceWelcome, self::MAX_INBOX_COUNT);

            // 如果當前數量已經等於應有數量，表示今天已發送過
            if ($currentInboxCount >= $shouldHaveCount) {
                $this->info("UID {$user->uid}: 今天已發送過獎勵信，跳過");
                continue;
            }

            try {
                DB::transaction(function () use ($user) {
                    // 檢查 InboxTargets 是否存在
                    if (!$this->checkInboxTargetExists($user)) {
                        $this->createInboxTarget($user);
                    }

                    // 發送獎勵信
                    $this->sendRegisterEventMail($user);
                }, 3);

                $processedCount++;
                $this->info("✓ UID {$user->uid}: 已發送第 " . ($currentInboxCount + 1) . " 封獎勵信");

            } catch (\Exception $e) {
                $this->error("✗ UID {$user->uid}: 發送失敗 - {$e->getMessage()}");
                \Log::error('PlayerRegisterEvent 發送失敗', [
                    'uid' => $user->uid,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("\n處理完成！成功發送: {$processedCount} 封");
        return 0;
    }

    // 檢查 InboxTargets 是否存在
    private function checkInboxTargetExists(Users $user): bool
    {
        return InboxTargets::where([
            'target_uid' => $user->uid,
            'inbox_messages_id' => self::REGISTER_EVENT_INBOX_ID,
        ])->exists();
    }

    // 創建 InboxTarget
    private function createInboxTarget(Users $user)
    {
        return InboxTargets::create([
            'target_uid' => $user->uid,
            'inbox_messages_id' => self::REGISTER_EVENT_INBOX_ID,
        ]);
    }

    // 發送獎勵信
    private function sendRegisterEventMail(Users $user)
    {
        return UserInboxEntries::create([
            'uid' => $user->uid,
            'inbox_messages_id' => self::REGISTER_EVENT_INBOX_ID,
            'status' => 'unread',
            'attachment_status' => 'unclaimed',
        ]);
    }

    // 檢查當前獎勵信數量
    private function checkInboxCount(Users $user): int
    {
        return UserInboxEntries::where([
            'uid' => $user->uid,
            'inbox_messages_id' => self::REGISTER_EVENT_INBOX_ID,
        ])->count();
    }
}
