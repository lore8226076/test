<?php

namespace App\Console\Commands;

use App\Models\Users;
use App\Service\PaymentService;
use Illuminate\Console\Command;

class TestShopPurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:shop-purchase {uid} {product_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '測試商城購買道具發放（包含月卡首購獎勵）';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uid = $this->argument('uid');
        $productId = $this->argument('product_id');

        // 取得用戶
        $user = Users::where('uid', $uid)->first();
        if (!$user) {
            $this->error("找不到用戶 UID: {$uid}");
            return Command::FAILURE;
        }

        $this->info("用戶: {$user->username} (UID: {$user->uid})");
        $this->info("商品 ID: {$productId}");
        $this->line('');

        // 調用 PaymentService 處理購買
        $result = PaymentService::processShopPurchase($user, $productId);

        // 顯示結果
        if ($result['status'] !== 'success') {
            $this->error('✗ 購買失敗: ' . ($result['error'] ?? 'unknown'));
            return Command::FAILURE;
        }

        // 月卡購買
        if (isset($result['type']) && $result['type'] === 'month_card') {
            $this->info('✓ 月卡購買成功');
            if ($result['is_first_purchase']) {
                $this->warn('★ 首次購買，已發放首購獎勵');
            }
            $this->table(
                ['項目', '值'],
                [
                    ['類型', '月卡'],
                    ['月卡 Key', $result['month_card_key']],
                    ['到期時間', $result['expire_at'] ?? '永久'],
                ]
            );
            return Command::SUCCESS;
        }

        // 一般道具購買
        $this->info('✓ 道具發放成功');
        if ($result['is_first_purchase']) {
            $this->warn('★ 首購獎勵！道具數量已加倍');
        }

        return Command::SUCCESS;
    }
}
