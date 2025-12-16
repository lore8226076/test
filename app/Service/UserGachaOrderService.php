<?php

namespace App\Service;

use App\Models\Gachas;
use App\Models\GddbSurgameHeroes;
use App\Models\Settings;
use App\Models\UserCharacter;
use App\Models\UserGachaOrderDetails;
use App\Models\UserGachaOrders;
use App\Models\UserGachaTimes;
use App\Models\UserItemLogs;
use App\Models\UserItems;
use App\Models\Users;
use Cache;
use DB;
use Illuminate\Support\Facades\Log;

class UserGachaOrderService extends AppService
{
    // 扭蛋數量不足的替代道具與對應數量
    const SUB_GACHA_ORDER_ITEM_ID = 100; // 商城幣

    const SUB_GACHA_ORDER_ITEM_ONE_PRICE_QTY = 100; // 100抽一次

    const SUB_GACHA_ORDER_ITEM_TEN_PRICE_QTY = 1000; // 1000是抽十次

    /**
     * 紙娃娃與家具扭蛋抽取
     *
     * @param  object  $user  用戶物件
     * @param  object  $gacha  卡池物件
     * @param  int  $times  抽取次數 (1 or 10)
     * @param  int  $price  價格
     * @param  bool  $useFree  是否使用免費抽取
     * @return array
     */
    public static function create($user, $gacha, $times, $price, $useFree = false)
    {
        $gachaDrawData = self::getSubGachaOrderItem($user, $gacha); // 取得替代道具資訊
        if (empty($gachaDrawData)) {
            return ['success' => 0, 'error_code' => 'GachaOrder:0012'];
        }

        $unitPrice = $gachaDrawData['one_price'] ?? $price;
        $checkResult = self::checkDrawRequirements($user, $gacha, $times, $useFree, $gachaDrawData['item_id'], $unitPrice);
        if (! $checkResult['success']) {
            return ['success' => 0, 'error_code' => $checkResult['error_code']];
        }

        $isFree = $checkResult['is_free'];
        $finalTotalCost = $checkResult['total_price'];

        // 快取設定值，減少對 Settings 查詢
        $ticket_mapping = Cache::remember('avatar_to_ticket', 600, function () {
            $setting = Settings::where('name', 'avatar_to_ticket')->first();

            return empty($setting) ? ['SSR' => 30, 'SR' => 7, 'R' => 1, 'N' => 1] : json_decode($setting->value, true);
        });

        DB::beginTransaction();
        try {
            $userGachaOrder = self::createUserGachaOrderRecord($user, $gacha, $times, $finalTotalCost, $isFree, $gachaDrawData['item_id']);
            $drawMemo = $gachaDrawData['is_change'] ? '扭蛋卡池抽取使用替代道具(item_id = '.$gachaDrawData['item_id'].')' : '扭蛋卡池抽取使用道具';

            // 只有付費抽取才扣除貨幣
            if (! $isFree) {
                $currencyResult = self::deductUserItem(UserItemLogs::TYPE_GACHA, $user, $gacha, $times, $finalTotalCost, $userGachaOrder, $gachaDrawData['item_id'], $drawMemo);

                if (! $currencyResult['success']) {
                    DB::rollBack();

                    return ['success' => 0, 'error_code' => $currencyResult['error_code']];
                }
            }

            // 抓保底次數，使用 `firstOrCreate()`
            $userGachaTime = UserGachaTimes::firstOrCreate(
                ['user_id' => $user->id, 'gacha_id' => $gacha->id],
                ['uid' => $user->uid, 'times' => 0]
            );

            // 查詢使用者已擁有的道具
            $userItemList = UserItems::where('user_id', $user->id)
                ->whereIn('item_id', function ($query) use ($gacha) {
                    $query->select('item_id')->from('gacha_details')->where('gacha_id', $gacha->id);
                })
                ->get()
                ->keyBy('item_id');

            $get_items = [];
            $userGachaDetails = [];
            $addItems = [];

            for ($i = 1; $i <= $times; $i++) {
                // 判斷下一抽要不要強制保底
                if ($gacha->max_times > 0) {
                    $is_guaranteed = $userGachaTime->times + 1 >= $gacha->max_times;
                } else {
                    $is_guaranteed = false;
                }

                $gachaDetail = self::drawGacha($gacha, $is_guaranteed);
                if (! $gachaDetail) {
                    DB::rollBack();

                    return ['success' => 0, 'error_code' => 'GachaOrder:0005'];
                }

                // 更新保底次數
                if ($gachaDetail->guaranteed) {
                    $userGachaTime->update(['times' => 0]);
                } else {
                    $userGachaTime->increment('times');
                }

                // 取得玩家是否已擁有該 item
                $ownedItem = $userItemList[$gachaDetail->item_id] ?? null;
                $alreadyOwned = ! is_null($ownedItem);
                $isAvatar = $ownedItem && $ownedItem->region === UserItems::REGION_AVATAR;
                $isSpecialItem = in_array($gachaDetail->item_id, ['101', '102']);  // 101, 102是遊戲票券
                $qty = $isSpecialItem ? $gachaDetail->qty : 1;

                if ($alreadyOwned && $isAvatar) {
                    // 抽到已擁有的 Avatar，轉換票券
                    $ticket_currency_item_id = 102;
                    $ticket_qty = $ticket_mapping[$gachaDetail->itemDetail->rarity] ?? 1;

                    $get_items[] = [
                        'item_id' => $gachaDetail->item_id,
                        'qty' => $qty,
                        'is_change' => $isSpecialItem ? 0 : 1,
                        'ticket_currency_item_id' => $ticket_currency_item_id,
                        'ticket_qty' => $isSpecialItem ? 0 : $ticket_qty,
                    ];

                    $userGachaDetails[] = [
                        'user_gacha_order_id' => $userGachaOrder->id,
                        'item_id' => $gachaDetail->item_id,
                        'is_change' => $isSpecialItem ? 1 : 0,
                        'change_item_id' => $ticket_currency_item_id,
                        'change_qty' => $isSpecialItem ? 0 : $ticket_qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                } else {
                    // 抽到新道具 or 非 avatar 類型道具，正常給
                    $get_items[] = [
                        'item_id' => $gachaDetail->item_id,
                        'qty' => $qty,
                        'is_change' => 0,
                        'ticket_currency_item_id' => 0,
                        'ticket_qty' => 0,
                    ];

                    $userGachaDetails[] = [
                        'user_gacha_order_id' => $userGachaOrder->id,
                        'item_id' => $gachaDetail->item_id,
                        'is_change' => 0,
                        'change_item_id' => null,
                        'change_qty' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // 若玩家未擁有該 item，加入已擁有清單，避免重複
                    if (! $alreadyOwned) {
                        $userItemList[$gachaDetail->item_id] = new UserItems([
                            'item_id' => $gachaDetail->item_id,
                            'region' => $gachaDetail->itemDetail->region ?? null,
                        ]);
                    }
                }
            }

            // 批量插入 `user_gacha_order_details`
            if ($userGachaDetails) {
                UserGachaOrderDetails::insert($userGachaDetails);
            }

            // 批量處理 `UserItemService::addItem`
            foreach ($get_items as $item) {
                $get_item_id = $item['is_change'] ? $item['ticket_currency_item_id'] : $item['item_id'];
                $get_item_qty = $item['is_change'] ? $item['ticket_qty'] : $item['qty'];

                // 如果已存在就累加
                if (isset($addItems[$get_item_id])) {
                    $addItems[$get_item_id]['qty'] += $get_item_qty;
                } else {
                    $addItems[$get_item_id] = [
                        'type' => UserItemLogs::TYPE_GACHA,
                        'user_id' => $user->id,
                        'uid' => $user->uid,
                        'item_id' => $get_item_id,
                        'qty' => $get_item_qty,
                        'is_lock' => 1,
                        'memo' => '扭蛋抽取',
                        'user_mall_order_id' => null,
                        'user_pay_order_id' => null,
                        'user_gacha_order_id' => $userGachaOrder->id,
                    ];
                }
            }

            $result = UserItemService::addItems($addItems);
            if (! $result['success']) {
                DB::rollBack();

                return ['success' => 0, 'error_code' => $result['error_code']];
            }

            DB::commit();

            return [
                'success' => 1,
                'get_items' => $get_items,
                'total_draws' => $times,
                'is_free' => $isFree,
                'pity' => self::getPityInfo($user, $gacha),
                'user_free_draw_info' => self::getFreeDrawInfoByGacha($user->uid, $gacha),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('扭蛋抽取失敗', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'gacha_id' => $gacha->id,
                'times' => $times,
                'price' => $price,
                'is_free' => $isFree ?? false,
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => 0, 'error_code' => 'GachaOrder:0006'];
        }
    }

    /**
     * 抽取英雄卡池
     *
     * @param  object  $user  用戶物件
     * @param  object  $gacha  卡池物件
     * @param  int  $times  抽取次數 (1 or 10)
     * @param  int  $price  價格
     * @param  bool  $useFree  是否使用免費抽取
     * @return array
     */
    public static function createCharacterGacha($user, $gacha, $times, $price, $useFree = false)
    {
        // 1. 取得替代道具與檢查條件
        $gachaDrawData = self::getSubGachaOrderItem($user, $gacha);
        if (empty($gachaDrawData)) {
            return ['success' => 0, 'error_code' => 'GachaOrder:0012'];
        }

        $unitPrice = $gachaDrawData['one_price'] ?? $price;
        $checkResult = self::checkDrawRequirements($user, $gacha, $times, $useFree, $gachaDrawData['item_id'], $unitPrice);
        if (! $checkResult['success']) {
            return ['success' => 0, 'error_code' => $checkResult['error_code']];
        }

        $isFree = $checkResult['is_free'];
        $finalTotalCost = $checkResult['total_price'];

        DB::beginTransaction();
        try {
            // 2. 建立訂單與扣款
            $userGachaOrder = self::createUserGachaOrderRecord($user, $gacha, $times, $finalTotalCost, $isFree, $gachaDrawData['item_id']);
            $drawMemo = $gachaDrawData['is_change'] ? '英雄卡池抽取使用替代道具(item_id = '.$gachaDrawData['item_id'].')' : '英雄卡池抽取使用道具';

            if (! $isFree) {
                $currencyResult = self::deductUserItem(UserItemLogs::TYPE_GACHA, $user, $gacha, $times, $finalTotalCost, $userGachaOrder, $gachaDrawData['item_id'], $drawMemo);

                if (! $currencyResult['success']) {
                    DB::rollBack();

                    return ['success' => 0, 'error_code' => $currencyResult['error_code']];
                }
            }

            // 3. 保底與新手邏輯
            $firstFreeGetSpecialHeroGachaId = self::getNewUserGachaId();
            $isFirstFreeDrawForGacha = $isFree &&
                $gacha->id == $firstFreeGetSpecialHeroGachaId &&
                UserCharacter::where(['uid' => $user->uid, 'character_id' => 1001])->doesntExist();

            $userGachaTime = UserGachaTimes::firstOrCreate(
                ['user_id' => $user->id, 'gacha_id' => $gacha->id],
                ['uid' => $user->uid, 'times' => 0]
            );

            $get_items = [];
            $userGachaDetails = [];

            // 4. 開始抽卡迴圈
            for ($i = 1; $i <= $times; $i++) {
                // A. 保底判斷
                if ($gacha->max_times > 0) {
                    $is_guaranteed = $userGachaTime->times + 1 >= $gacha->max_times;
                } else {
                    $is_guaranteed = false;
                }

                // B. 抽取
                if ($isFirstFreeDrawForGacha) {
                    $gachaDetail = $gacha->gachaDetails->firstWhere('item_id', 1001001);
                    if (! $gachaDetail) {
                        $gachaDetail = self::drawGacha($gacha, $is_guaranteed);
                    }
                } else {
                    $gachaDetail = self::drawGacha($gacha, $is_guaranteed);
                }

                if (! $gachaDetail) {
                    DB::rollBack();

                    return ['success' => 0, 'error_code' => 'GachaOrder:0005'];
                }

                // C. 更新保底次數
                if ($gachaDetail->guaranteed) {
                    $userGachaTime->update(['times' => 0]);
                } else {
                    $userGachaTime->increment('times');
                }

                // D. 給英雄or給碎片 (整合 addItem)
                $characterId = GddbSurgameHeroes::getCharacterIdByItemId($gachaDetail->item_id);
                $memo = '英雄卡池抽取獲得';

                $addItemResult = UserItemService::addItem(
                    UserItemLogs::TYPE_GACHA,
                    $user->id,
                    $user->uid,
                    $gachaDetail->item_id,
                    1,
                    1,
                    $memo,
                    null, null, $userGachaOrder->id
                );

                if ($addItemResult['success'] != 1) {
                    DB::rollBack();

                    return ['success' => 0, 'error_code' => $addItemResult['error_code'] ?? 'GachaOrder:0006'];
                }

                // E. 準備回傳資料與訂單明細
                if ($characterId) {
                    // --- 英雄類邏輯 ---

                    // 檢查是否真的獲得了新英雄
                    $isNewHero = isset($addItemResult['is_hero']) && $addItemResult['is_hero'] === true;

                    if ($isNewHero) {
                        // 情境: 獲得新英雄
                        $get_items[] = [
                            'item_id' => $gachaDetail->item_id,
                            'qty' => 1,
                            'is_change' => 0,
                            'ticket_currency_item_id' => 0,
                            'ticket_qty' => 0,
                        ];

                        $userGachaDetails[] = [
                            'user_gacha_order_id' => $userGachaOrder->id,
                            'item_id' => $gachaDetail->item_id,
                            'is_change' => 0,
                            'change_item_id' => null,
                            'change_qty' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        $fragmentId = GddbSurgameHeroes::getFragmentIdByCharacterId($characterId);

                        $get_items[] = [
                            'item_id' => $gachaDetail->item_id,
                            'qty' => 1,
                            'is_change' => 1,
                            'ticket_currency_item_id' => $fragmentId,
                            'ticket_qty' => 1,
                        ];

                        $userGachaDetails[] = [
                            'user_gacha_order_id' => $userGachaOrder->id,
                            'item_id' => $gachaDetail->item_id,
                            'is_change' => 1,
                            'change_item_id' => $fragmentId,
                            'change_qty' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                } else {
                    // --- 非英雄的一般道具 ---
                    $get_items[] = [
                        'item_id' => $gachaDetail->item_id,
                        'qty' => 1,
                        'is_change' => 0,
                        'ticket_currency_item_id' => 0,
                        'ticket_qty' => 0,
                    ];

                    $userGachaDetails[] = [
                        'user_gacha_order_id' => $userGachaOrder->id,
                        'item_id' => $gachaDetail->item_id,
                        'is_change' => 0,
                        'change_item_id' => null,
                        'change_qty' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // 5. 批量插入訂單明細
            if ($userGachaDetails) {
                UserGachaOrderDetails::insert($userGachaDetails);
            }

            DB::commit();

            return [
                'success' => 1,
                'get_items' => $get_items,
                'total_draws' => $times,
                'is_free' => $isFree,
                'pity' => self::getPityInfo($user, $gacha),
                'user_free_draw_info' => self::getFreeDrawInfoByGacha($user->uid, $gacha),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('英雄卡池抽取失敗', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'gacha_id' => $gacha->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => 0, 'error_code' => 'GachaOrder:0006'];
        }
    }

    /**
     * 取得某玩家在某卡池距離保底還需要幾抽
     */
    public static function getRemainingPityTimes(Users $user, Gachas $gacha): int
    {
        $userGachaTime = UserGachaTimes::firstOrCreate(
            ['user_id' => $user->id, 'gacha_id' => $gacha->id],
            ['uid' => $user->uid, 'times' => 0]
        );

        $currentTimes = (int) $userGachaTime->times;
        $maxTimes = (int) $gacha->max_times;

        if ($maxTimes <= 0) {
            return 0;
        }

        $remaining = $maxTimes - $currentTimes;

        return max($remaining, 1);
    }

    protected static function drawGacha($gacha, $is_guaranteed)
    {
        $details = $gacha->gachaDetails;
        $totalWeight = 0;
        $weightedPool = [];

        foreach ($details as $detail) {
            if (! $is_guaranteed || ($is_guaranteed && $detail->guaranteed)) {
                $totalWeight += round($detail->percent * 100);
                $weightedPool[] = ['item' => $detail, 'weight' => $totalWeight];
            }
        }

        if ($totalWeight <= 0) {
            Log::error('抽卡失敗，卡池總機率為零', [
                'gacha_id' => $gacha->id,
                'is_guaranteed' => $is_guaranteed,
            ]);

            return null;
        }

        $randomNumber = mt_rand(1, $totalWeight);

        foreach ($weightedPool as $entry) {
            if ($randomNumber <= $entry['weight']) {
                return $entry['item'];
            }
        }

        Log::error('抽卡失敗，未取得任何獎勵', [
            'gacha_id' => $gacha->id,
            'is_guaranteed' => $is_guaranteed,
            'random_number' => $randomNumber,
        ]);

        return null;
    }

    /**
     * 取得卡池保底資訊
     */
    public static function getPityInfo(Users $user, Gachas $gacha): array
    {
        if ((int) $gacha->max_times <= 0) {
            return [
                'current' => 0,
                'max' => 0,
                'remaining' => 0,
            ];
        }

        $userGachaTime = UserGachaTimes::where('user_id', $user->id)
            ->where('gacha_id', $gacha->id)
            ->first();

        $currentTimes = $userGachaTime ? $userGachaTime->times : 0;
        $maxTimes = (int) $gacha->max_times;

        return [
            'current' => $currentTimes,
            'max' => $maxTimes,
            'remaining' => self::getRemainingPityTimes($user, $gacha),
        ];
    }

    /**
     * 檢查是否還有免費抽取次數（每日 00:00 重置）
     *
     * @param  int  $uid  用戶 UID
     * @param  object  $gacha  卡池物件
     * @return array ['available' => bool, 'used' => int, 'total' => int]
     */
    public static function checkFreeDrawAvailable($uid, $gacha): array
    {
        $freeDrawLimit = (int) ($gacha->free_draw_times ?? 0);

        if ($freeDrawLimit <= 0) {
            return [
                'available' => false,
                'used' => 0,
                'total' => 0,
            ];
        }

        // 計算今日時間範圍（00:00:00 ~ 23:59:59）
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        $usedFreeDraws = UserGachaOrders::where('uid', $uid)
            ->where('gacha_id', $gacha->id)
            ->where('is_free', 1)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();

        return [
            'available' => $usedFreeDraws < $freeDrawLimit,
            'used' => $usedFreeDraws,
            'total' => $freeDrawLimit,
        ];
    }

    /**
     * 取得免費抽取資訊（每日 00:00 重置）
     *
     * @param  int  $uid  用戶 UID
     * @param  object  $gacha  卡池物件
     */
    public static function getFreeDrawInfoByGacha($uid, $gacha): array
    {
        $freeDrawCheck = self::checkFreeDrawAvailable($uid, $gacha);

        // 計算下次重置時間（隔天 00:00:00）
        $nextResetTime = now()->addDay()->startOfDay();
        $secondsUntilReset = now()->diffInSeconds($nextResetTime);

        return [
            'has_free_draw' => $freeDrawCheck['available'] ? 1 : -1,
            'used' => $freeDrawCheck['used'],
            'total' => $freeDrawCheck['total'],
            'remaining' => max(0, $freeDrawCheck['total'] - $freeDrawCheck['used']),
            'next_reset_seconds' => $secondsUntilReset,
        ];
    }

    /**
     * 指定扭蛋機Id為新手使用扭蛋機
     */
    private static function getNewUserGachaId()
    {
        return Gachas::where([
            'is_active' => 1,
            'type' => 1,
        ])->orderBy('id', 'asc')->first()->id;
    }

    /**
     * 扭蛋道具需求道具
     * 1. 主要道具不夠時，回傳指定道具ID與數量
     */
    public static function getSubGachaOrderItem(Users $user, Gachas $gacha): ?array
    {
        $currentItem = UserItems::getUserFormattedItems($user->uid, [$gacha->currency_item_id]);
        $currentQty = $currentItem[0]['amount'] ?? 0;
        // 判斷是否有足夠主要道具
        if ($currentQty >= $gacha->one_price) {
            return [
                'item_id' => $gacha->currency_item_id,
                'one_price' => $gacha->one_price,
                'ten_price' => $gacha->ten_price,
                'is_change' => false,
            ];
        } else {
            return [
                'item_id' => self::SUB_GACHA_ORDER_ITEM_ID,
                'one_price' => self::SUB_GACHA_ORDER_ITEM_ONE_PRICE_QTY,
                'ten_price' => self::SUB_GACHA_ORDER_ITEM_TEN_PRICE_QTY,
                'is_change' => true,
            ];
        }

        return null;
    }

    /**
     * 建立扭蛋主紀錄
     */
    protected static function createUserGachaOrderRecord($user, $gacha, $times, $price, $isFree, $useItemId)
    {
        $userGachaOrder = UserGachaOrders::create([
            'user_id' => $user->id,
            'uid' => $user->uid,
            'gacha_id' => $gacha->id,
            'type' => $gacha->type,
            'times' => $times,
            'currency_item_id' => $useItemId,
            'currency_amount' => $price,
            'is_free' => $isFree ? 1 : 0,
        ]);

        return $userGachaOrder;
    }

    /**
     * 扭蛋扣除道具
     */
    protected static function deductUserItem($drawType, $user, $gacha, $times, $price, $userGachaOrder, $useItemId, $drawMemo)
    {
        $currencyResult = UserItemService::removeItem(
            $drawType,
            $user->id,
            $user->uid,
            $useItemId,
            $price,
            1,
            $drawMemo,
            null,
            null,
            $userGachaOrder->id
        );

        return $currencyResult;
    }

    /**
     * 檢查轉蛋條件 (免費次數檢查 或 付費額度檢查)
     *
     * @param  object  $user  使用者物件
     * @param  object  $gacha  轉蛋設定物件
     * @param  int  $times  抽取次數
     * @param  bool  $useFree  是否使用免費次數
     * @param  int  $currencyItemId  消耗的貨幣 Item ID
     * @param  int  $unitPrice  單次抽取價格
     */
    protected static function checkDrawRequirements($user, $gacha, int $times, bool $useFree, int $currencyItemId, int $unitPrice): array
    {
        // === 情境 A: 使用免費抽取 ===
        if ($useFree) {
            // 1. 檢查是否有免費次數
            $freeDrawCheck = self::checkFreeDrawAvailable($user->uid, $gacha);

            if (! $freeDrawCheck['available']) {
                return ['success' => 0, 'error_code' => 'GachaOrder:0010']; // 免費抽取次數已用完
            }

            // 2. 免費抽取限制只能單抽
            if ($times != 1) {
                return ['success' => 0, 'error_code' => 'GachaOrder:0011']; // 免費抽取只能單抽
            }

            // 驗證通過
            return [
                'success' => 1,
                'is_free' => true,
                'total_price' => 0, // 免費不扣款
            ];
        }

        // === 情境 B: 一般付費抽取 ===

        // 計算總價
        $totalPrice = $unitPrice * $times;

        // 1. 查詢用戶持有的貨幣數量
        $userCurrencyItem = UserItems::where('user_id', $user->id)
            ->where('item_id', $currencyItemId)
            ->select('id', 'qty')
            ->first();

        // 2. 檢查餘額
        if (! $userCurrencyItem || $userCurrencyItem->qty < $totalPrice) {
            return ['success' => 0, 'error_code' => 'MallOrder:0010']; // 餘額不足
        }

        // 驗證通過
        return [
            'success' => 1,
            'is_free' => false,
            'total_price' => $totalPrice, // 回傳總扣款金額供後續扣除使用
        ];
    }
}
