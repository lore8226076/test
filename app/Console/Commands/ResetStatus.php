<?php

namespace App\Console\Commands;

use App\Models\UserStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetStatus extends Command
{
    protected $signature = 'status:reset';

    protected $description = '重置使用者狀態';

    public function handle()
    {
        // 重置掃蕩次數
        $this->resetCounter('sweep', '掃蕩次數');

        // 重置巡邏次數
        $this->resetCounter('patrol', '巡邏次數');

        // 重置資源關卡次數
        $this->resetResourceStageCount();

        return self::SUCCESS;
    }

    /**
     * 重置使用者計數器
     *
     * @param  string  $type  計數器類型 (sweep, patrol)
     * @param  string  $displayName  顯示名稱
     */
    private function resetCounter(string $type, string $displayName): void
    {
        $countField = "{$type}_count";
        $maxField = "{$type}_max";

        $this->info("開始重置{$displayName}...");
        Log::info("【{$displayName}】開始重置");

        $userStatuses = UserStatus::whereColumn($countField, '<', $maxField)->get();

        foreach ($userStatuses as $userStatus) {
            Log::info("【{$displayName}】重置使用者", [
                'uid' => $userStatus->uid,
                'before' => $userStatus->$countField,
                'after' => $userStatus->$maxField,
            ]);

            $userStatus->$countField = $userStatus->$maxField;
            $userStatus->save();
        }

        $count = $userStatuses->count();
        $this->info("已重置 {$count} 位使用者的{$displayName}");
        Log::info("【{$displayName}】重置完成", ['count' => $count]);
    }

    /**
     * 重置資源關卡次數
     */
    private function resetResourceStageCount(): void
    {
        $this->info('開始重置資源關卡次數...');
        Log::info('【資源關卡】開始重置');

        $userInfos = \App\Models\UserSurGameInfo::all();

        foreach ($userInfos as $userInfo) {
            $changes = [];

            // 重置金幣掃蕩次數
            if ($userInfo->money_sweep_free_left < $userInfo->money_sweep_free_total) {
                $changes['money_sweep_free_left'] = [
                    'before' => $userInfo->money_sweep_free_left,
                    'after' => $userInfo->money_sweep_free_total,
                ];
                $userInfo->money_sweep_free_left = $userInfo->money_sweep_free_total;
            }
            if ($userInfo->money_sweep_pay_left < $userInfo->money_sweep_pay_total) {
                $changes['money_sweep_pay_left'] = [
                    'before' => $userInfo->money_sweep_pay_left,
                    'after' => $userInfo->money_sweep_pay_total,
                ];
                $userInfo->money_sweep_pay_left = $userInfo->money_sweep_pay_total;
            }

            // 重置經驗掃蕩次數
            if ($userInfo->exp_sweep_free_left < $userInfo->exp_sweep_free_total) {
                $changes['exp_sweep_free_left'] = [
                    'before' => $userInfo->exp_sweep_free_left,
                    'after' => $userInfo->exp_sweep_free_total,
                ];
                $userInfo->exp_sweep_free_left = $userInfo->exp_sweep_free_total;
            }
            if ($userInfo->exp_sweep_pay_left < $userInfo->exp_sweep_pay_total) {
                $changes['exp_sweep_pay_left'] = [
                    'before' => $userInfo->exp_sweep_pay_left,
                    'after' => $userInfo->exp_sweep_pay_total,
                ];
                $userInfo->exp_sweep_pay_left = $userInfo->exp_sweep_pay_total;
            }

            // 重置裝備掃蕩次數
            if ($userInfo->gift_sweep_free_left < $userInfo->gift_sweep_free_total) {
                $changes['gift_sweep_free_left'] = [
                    'before' => $userInfo->gift_sweep_free_left,
                    'after' => $userInfo->gift_sweep_free_total,
                ];
                $userInfo->gift_sweep_free_left = $userInfo->gift_sweep_free_total;
            }
            if ($userInfo->gift_sweep_pay_left < $userInfo->gift_sweep_pay_total) {
                $changes['gift_sweep_pay_left'] = [
                    'before' => $userInfo->gift_sweep_pay_left,
                    'after' => $userInfo->gift_sweep_pay_total,
                ];
                $userInfo->gift_sweep_pay_left = $userInfo->gift_sweep_pay_total;
            }

            // 如果有變更才儲存
            if (! empty($changes)) {
                Log::info('【資源關卡】重置使用者', [
                    'uid' => $userInfo->uid,
                    'changes' => $changes,
                ]);

                $userInfo->save();
            }
        }

        $count = $userInfos->count();
        $this->info("已重置 {$count} 位使用者的資源關卡次數");
        Log::info('【資源關卡】重置完成', ['count' => $count]);
    }
}
