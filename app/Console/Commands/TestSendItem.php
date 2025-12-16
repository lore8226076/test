<?php

namespace App\Console\Commands;

use App\Models\MonthCardConfig;
use App\Models\UserMonthCard;
use App\Models\Users;
use App\Service\MonthCardService;
use App\Service\UserItemService;
use Illuminate\Console\Command;

class TestSendItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:buy-month-card {uid?} {product_id?} {--reset : 重置用戶的月卡資料} {--reset-all : 重置用戶的所有月卡資料}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '測試月卡購買流程（跳過金流驗證）';

    /**
     * 預設值
     */
    protected $defaultUid = '1678748024';
    protected $defaultProductId = 'forever001';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uid = $this->argument('uid') ?? $this->defaultUid;
        $productId = $this->argument('product_id') ?? $this->defaultProductId;

        // 取得用戶
        $user = Users::where('uid', $uid)->first();
        if (! $user) {
            $this->error("找不到用戶: {$uid}");
            return 1;
        }

        // 重置所有月卡
        if ($this->option('reset-all')) {
            return $this->resetAllMonthCards($user);
        }

        // 重置指定月卡
        if ($this->option('reset')) {
            return $this->resetMonthCard($user, $productId);
        }

        // 正常購買流程
        return $this->purchaseMonthCard($user, $productId);
    }

    /**
     * 重置用戶指定月卡資料
     */
    protected function resetMonthCard(Users $user, string $productId): int
    {
        $monthCardConfig = MonthCardConfig::where('key', $productId)
            ->where('is_active', true)
            ->first();

        if (! $monthCardConfig) {
            $this->error("找不到月卡設定: {$productId}");
            return 1;
        }

        $deleted = UserMonthCard::where('uid', $user->uid)
            ->where('month_card_config_id', $monthCardConfig->id)
            ->delete();

        if ($deleted) {
            $this->info("✓ 已重置用戶 {$user->uid} 的月卡: {$monthCardConfig->localization_name}");
        } else {
            $this->warn("用戶 {$user->uid} 沒有此月卡記錄");
        }

        return 0;
    }

    /**
     * 重置用戶所有月卡資料
     */
    protected function resetAllMonthCards(Users $user): int
    {
        $deleted = UserMonthCard::where('uid', $user->uid)->delete();

        $this->info("✓ 已重置用戶 {$user->uid} 的所有月卡資料（刪除 {$deleted} 筆）");

        return 0;
    }

    /**
     * 執行月卡購買流程
     */
    protected function purchaseMonthCard(Users $user, string $productId): int
    {
        $this->info("測試參數：");
        $this->line("  UID: {$user->uid}");
        $this->line("  Product ID: {$productId}");
        $this->newLine();

        $this->info("用戶資訊：");
        $this->line("  ID: {$user->id}");
        $this->line("  UID: {$user->uid}");
        $this->line("  Name: {$user->name}");
        $this->newLine();

        // 取得月卡設定
        $monthCardConfig = MonthCardConfig::where('key', $productId)
            ->where('is_active', true)
            ->first();

        if (! $monthCardConfig) {
            $this->error("找不到月卡設定: {$productId}");
            $this->line("可用的月卡：");
            MonthCardConfig::where('is_active', true)->get()->each(function ($config) {
                $this->line("  - {$config->key} ({$config->localization_name})");
            });
            return 1;
        }

        $this->info("月卡設定：");
        $this->line("  ID: {$monthCardConfig->id}");
        $this->line("  名稱: {$monthCardConfig->localization_name}");
        $this->line("  增加天數: {$monthCardConfig->add_days}");
        $this->line("  首購獎勵: " . json_encode($monthCardConfig->basic_reward));
        $this->line("  每日獎勵: " . json_encode($monthCardConfig->daily_reward));
        $this->newLine();

        // 執行購買
        $this->info("執行購買流程...");
        $result = MonthCardService::purchaseMonthCard($user, $monthCardConfig);

        $this->newLine();
        $this->info("購買結果：");
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (! $result['success']) {
            $this->error("✗ 購買失敗！錯誤碼: {$result['error_code']}");
            return 1;
        }

        // 發放首購獎勵
        if ($result['is_first_purchase'] && ! empty($result['basic_reward'])) {
            $this->newLine();
            $this->info("發放首購獎勵...");
            $rewards = MonthCardService::formatRewards($result['basic_reward']);

            foreach ($rewards as $reward) {
                $itemResult = UserItemService::addItem(
                    12,
                    $user->id,
                    $user->uid,
                    $reward['item_id'],
                    $reward['amount'],
                    1,
                    '月卡首購獎勵（測試）'
                );

                $status = $itemResult['success'] ? '✓' : '✗';
                $this->line("  {$status} Item {$reward['item_id']} x {$reward['amount']}");
            }
        }

        $this->newLine();
        $this->info("✓ 測試完成！");
        $this->line("  首購: " . ($result['is_first_purchase'] ? '是' : '否'));
        $this->line("  到期時間: " . ($result['expire_at'] ?? '永久'));
        $this->line("  累計購買次數: {$result['total_purchase_times']}");

        return 0;
    }
}
