<?php
namespace App\Service;

use App\Models\GddbItems;
use App\Models\GddbSurgameItemPackage;
use App\Models\GddbSurgamePassiveReward;
use App\Models\UserItemLogs;
use App\Models\UserJourneyRecord;
use App\Models\Users;
use Exception;
use Illuminate\Support\Facades\Log;

class ItemPackageService
{
    const ITEM_ID_COIN    = 101; // 金幣
    const ITEM_ID_EXP     = 199; // 經驗
    const ITEM_ID_CRYSTAL = 198; // 水晶
    const ITEM_ID_PAINT   = 191; // 顏料
    const ITEM_ID_XP      = 190; // XP

    // 檢查是否為禮包/寶箱
    public function isPackageItem($packageItemId)
    {
        return GddbSurgameItemPackage::where('item_id', $packageItemId)->exists();
    }

    public function openPackage(Users $user, $packageItemId, $selectedItemIds, $amount = 1)
    {
        // 0. 基礎檢查
        $itemInfo = GddbItems::where('item_id', $packageItemId)->first();
        if (! $itemInfo) {
            return ['success' => 0, 'error_code' => 'INVENTORY:0001'];
        }

        // 1. 取得扣除禮包所需數量
        $necessary      = GddbSurgameItemPackage::where('item_id', $packageItemId)->first()?->use_necessary ?? 1;
        $totalNecessary = $necessary * $amount;

        $removeResult = UserItemService::removeItem(
            UserItemLogs::TYPE_ITEM_PACKAGE,
            $user->id,
            $user->uid,
            $packageItemId,
            $totalNecessary,
            1,
            '使用禮包'
        );

        if ($removeResult['success'] === 0) {
            return ['success' => 0, 'error_code' => 'INVENTORY:0004'];
        }

        // 資源包計算邏輯
        if ($itemInfo->type === 'MoneyPack' || $itemInfo->type === 'ExpPack') {
            try {
                $reward = $this->calculateTimeResource($user, $itemInfo, $amount);
                return ['success' => 1, 'rewards' => [$reward]];
            } catch (Exception $e) {
                Log::error('資源包計算異常', ['uid' => $user->uid, 'item_id' => $packageItemId, 'error' => $e->getMessage()]);
                throw $e;
            }
        }

        // 一般禮包邏輯
        $allRewards = [];

        if (! empty($selectedItemIds)) {
            $rewards = $this->getPackageRewards($packageItemId, $selectedItemIds);
            if (! $rewards) {
                Log::error('無效的自選禮包或獎勵', ['packageItemId' => $packageItemId]);
                throw new \Exception('無效的自選禮包或獎勵');
            }
            foreach ($rewards as $reward) {
                $allRewards[$reward['item_id']] = $reward;
            }
        } else {
            for ($i = 0; $i < $amount; $i++) {
                $rewards = $this->getPackageRewards($packageItemId, []);
                if (! $rewards) {
                    Log::error('無效的隨機禮包或獎勵', ['packageItemId' => $packageItemId]);
                    throw new \Exception('無效的隨機禮包或獎勵');
                }
                foreach ($rewards as $reward) {
                    if (! isset($allRewards[$reward['item_id']])) {
                        $allRewards[$reward['item_id']] = $reward;
                    } else {
                        $allRewards[$reward['item_id']]['qty'] += $reward['qty'];
                    }
                }
            }
        }

        return ['success' => 1, 'rewards' => array_values($allRewards)];
    }

    /**
     * 計算時間型資源包收益 (金幣包/經驗包)
     */
    private function calculateTimeResource(Users $user, GddbItems $item, int $amount)
    {
        // 1. 定義道具時數對照 (Lookup Table)
        $itemConfig = [
            500 => 2, 510  => 2,
            501 => 4, 511  => 4,
            502 => 8, 512  => 8,
            503 => 12, 513 => 12,
            504 => 24, 514 => 24,
        ];

        $hours = $itemConfig[$item->item_id] ?? 0;

        if ($hours <= 0) {
            throw new Exception("未定義的資源包時數: " . $item->item_id);
        }

        // 2. 取得當前進度
        $currentStageId = UserJourneyRecord::where('uid', $user->uid)
            ->latest('id')
            ->value('current_journey_id') ?? 1;

        // 3. 讀取該章節收益
        $passiveData = GddbSurgamePassiveReward::where('now_stage', $currentStageId)->first();

        if (! $passiveData) {
            Log::warning("查無章節 {$currentStageId} 的被動收益資料");
            return [
                'item_id' => ($item->type === 'MoneyPack') ? self::ITEM_ID_COIN : self::ITEM_ID_EXP,
                'qty'     => 0,
            ];
        }

        // 4. 計算數量
        $targetItemId = ($item->type === 'MoneyPack') ? self::ITEM_ID_COIN : self::ITEM_ID_EXP;
        $hourlyRate   = ($item->type === 'MoneyPack') ? $passiveData->hour_coin : $passiveData->hour_exp;

        // 總量 = 時產量 * 時數 * 數量
        $totalQty = (int) floor($hourlyRate * $hours * $amount);

        return [
            'item_id' => $targetItemId,
            'qty'     => $totalQty,
        ];
    }

    // 是否為自動開啟禮包
    public function isAutoOpen($packageItemId)
    {
        return GddbSurgameItemPackage::where('item_id', $packageItemId)->first()?->auth_use === 1;
    }

    // 所需數量是否足夠
    public function hasEnoughForOpen($uid, $packageItemId)
    {
        $currentCount = UserItems::where('uid', $uid)->where('item_id', $packageItemId)->first()?->qty ?? 0;
        $necessary    = GddbSurgameItemPackage::where('item_id', $packageItemId)->first()?->use_necessary ?? 1;

        return $CurrentCount >= $necessary;
    }

    // 是否為自選禮包
    public function isChoiceBox($packageItemId)
    {
        return GddbSurgameItemPackage::where('item_id', $packageItemId)->first()?->choice_box === 1;
    }

    // 取得禮包獎勵(自選禮包需有item_id, 非自選為隨機)
    public function getPackageRewards($packageItemId, $selectedItemIds = [])
    {
        $package = GddbSurgameItemPackage::where('item_id', $packageItemId)->first();
        if (! $package) {
            return null; // 找不到禮包
        }

        // 轉換 JSON
        $contents = json_decode('[' . $package->contents . ']', true);
        $contents = $this->transformContents($contents);
        if (! is_array($contents)) {
            return null; // 內容格式錯誤
        }

        // ===== 開始 =====
        if (! empty($selectedItemIds)) {
            // ===== 自選禮包 =====
            if (! $package->choice_box) {
                return null; // 不是自選禮包
            }

            $selectionMap = [];
            foreach ($selectedItemIds as $selection) {
                $itemId = (int) $selection[0];
                $times  = (int) $selection[1];

                if (isset($selectionMap[$itemId])) {
                    $selectionMap[$itemId] += $times;
                } else {
                    $selectionMap[$itemId] = $times;
                }
            }

            $rewards = [];
            foreach ($contents as $content) {
                $contentItemId = $content['item_id'];

                // 2. 檢查禮包內容的 item_id 是否在玩家的選擇中
                if (isset($selectionMap[$contentItemId])) {

                    // 3. 取得玩家選擇的 "總次數"
                    $totalTimes = $selectionMap[$contentItemId];

                    // 4. 計算最終獎勵: (禮包基礎數量 * 玩家選擇總次數)
                    $rewards[] = [
                        'item_id' => $contentItemId,
                        'qty'     => $content['qty'] * $totalTimes, // 應用乘數
                    ];
                }
            }

            if (empty($rewards) || count($rewards) != count($selectionMap)) {
                \Log::error('自選禮包獎勵匹配失敗', [
                    'package_id' => $packageItemId,
                    'contents'   => $contents,
                    'selection'  => $selectedItemIds,
                ]);

                return null; // 選擇的道具不在禮包內
            }

            return $rewards;

        } else {
            // ===== 隨機禮包 =====
            if ($package->choice_box) {
                return null; // 不是隨機禮包
            }

            $randomTimes = (int) $package->random_times; // 抽取次數

            if ($randomTimes === 0) {
                // 0代表全拿
                return $contents;
            }

            // 每次呼叫都要重新打亂
            shuffle($contents);

            // 從打亂後的內容取 randomTimes 個
            return array_slice($contents, 0, $randomTimes);
        }

        return null;
    }

    // 資料轉換
    private function transformContents($contents)
    {
        $result = [];

        if (! is_array($contents)) {
        }

        foreach ($contents as $content) {
            if (is_array($content) && count($content) == 2) {
                $result[] = [
                    'item_id' => (int) $content[0],
                    'qty'     => (int) $content[1],
                ];
            }
        }

        return $result;
    }
}
