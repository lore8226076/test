<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CharacterDeploySlot;
use App\Models\GddbItems;
use App\Models\UserEquipmentSession;
use App\Models\UserItemLogs;
use App\Models\UserItems;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\ItemPackageService;
use App\Service\SurgameEquipmentService;
use App\Service\UserItemService;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Collection;

class PackageController extends Controller
{
    public function __construct(Request $request)
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => []]);
        }
    }

    public function getAllItems(Request $request)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        // 非裝備
        // 1. 準備稀有度的自訂排序 SQL
        // UR(1) > MR(2) > SSR(3) > SR(4) > R(5) > UN(6) > N(7) > Null/Other(8)
        $rarityOrder = "CASE gddb_items.rarity
            WHEN 'UR' THEN 1
            WHEN 'MR' THEN 2
            WHEN 'SSR' THEN 3
            WHEN 'SR' THEN 4
            WHEN 'R' THEN 5
            WHEN 'UN' THEN 6
            WHEN 'N' THEN 7
            ELSE 8
            END";

        $items = UserItems::with(['item.itemPackage'])
            ->join('gddb_items', 'user_items.item_id', '=', 'gddb_items.item_id')

            // --- 查詢條件 ---
            ->where('user_items.user_id', $user->id)
            ->where('user_items.qty', '>', 0)
            ->where('gddb_items.region', 'Surgame')
            ->whereNotIn('gddb_items.category', ['Equipment', 'Shard', 'Treasure', 'Hero'])

            // --- 排序邏輯 ---
            // 1. 稀有度 (Rarity): 自訂順序 (最優先)
            ->orderByRaw($rarityOrder)
            // 2. 類別 (Category): 英數 A-Z
            ->orderBy('gddb_items.category', 'asc')
            // 3. 類型 (Type): 英數 A-Z (在稀有度相同時，才把 type 相同的放一起)
            ->orderBy('gddb_items.type', 'asc')
            // 4. 道具 ID (Item ID): 數字 小到大
            ->orderBy('gddb_items.item_id', 'asc')
            // 5. 屬性 (Element): 數字 小到大 (如果 gddb_items 上有此欄位)
            // ->orderBy('gddb_items.element', 'asc')

            // --- 收尾 ---
            ->select('user_items.*')
            ->get();
        $items = $this->formatUserInventory($items);

        // 額外加入特定 item_id (方便前端作業)
        $items = $this->appendSpecialItems($items, $user->id, [100, 101, 102, 1201]);

        // 裝備
        $equipments = UserEquipmentSession::with('attributes')->where('uid', $user->uid)->get();
        $equipments = $this->formatUserEquipments($equipments);

        // 符文
        $runes = []; // TODO: 符文系統尚未開發

        // 碎片
        $shard = UserItems::with(['item.itemPackage'])
            ->join('gddb_items', 'user_items.item_id', '=', 'gddb_items.item_id')
            ->where('user_id', $user->id)
            ->where('user_items.region', 'Surgame')
            ->where('gddb_items.category', 'Shard')

            // --- 查詢條件 ---
            ->where('user_items.user_id', $user->id)
            ->where('gddb_items.region', 'Surgame')

            // --- 排序邏輯 ---
            // 1. 稀有度 (Rarity): 自訂順序 (最優先)
            ->orderByRaw($rarityOrder)
            // 2. 類別 (Category): 英數 A-Z
            ->orderBy('gddb_items.category', 'asc')
            // 3. 類型 (Type): 英數 A-Z (在稀有度相同時，才把 type 相同的放一起)
            ->orderBy('gddb_items.type', 'asc')
            // 4. 道具 ID (Item ID): 數字 小到大
            ->orderBy('gddb_items.item_id', 'asc')
            ->get();
        $shard = $this->formatUserShards($shard);

        // 寶物
        $treasures = UserItems::with(['item.itemPackage'])
            ->join('gddb_items', 'user_items.item_id', '=', 'gddb_items.item_id')

            ->where('user_id', $user->id)
            ->where('gddb_items.region', 'Surgame')
            ->where('gddb_items.category', 'Treasure')

            // --- 查詢條件 ---
            ->where('user_items.user_id', $user->id)
            ->where('gddb_items.region', 'Surgame')

            // --- 排序邏輯 ---
            // 1. 稀有度 (Rarity): 自訂順序 (最優先)
            ->orderByRaw($rarityOrder)
            // 2. 類別 (Category): 英數 A-Z
            ->orderBy('gddb_items.category', 'asc')
            // 3. 類型 (Type): 英數 A-Z (在稀有度相同時，才把 type 相同的放一起)
            ->orderBy('gddb_items.type', 'asc')
            // 4. 道具 ID (Item ID): 數字 小到大
            ->orderBy('gddb_items.item_id', 'asc')
            ->get();
        $treasures = $this->formatUserTreasure($treasures);

        $results = $this->formatPackages($items, $equipments, $runes, $shard, $treasures);

        return response()->json(['data' => $results], 200);
    }

    // 使用背包物品
    public function useItem(Request $request, ItemPackageService $svc)
    {
        $user = Users::where('uid', auth()->guard('api')->user()?->uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        $itemId = (int) $request->input('item_id', 0);
        if ($itemId <= 0) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INVENTORY:0001'), 422);
        }
        $amount = (int) $request->input('amount', 1);
        if ($amount <= 0) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INVENTORY:0004'), 422);
        }

        $selectedItemIds = $this->formatterSelectedIds($request->input('selected_item_id', []));

        // 如果是自選包, 檢查陣列格式並加總 "times"
        if (! empty($selectedItemIds)) {
            $totalTimes = 0;
            foreach ($selectedItemIds as $selection) {
                if (
                    ! is_array($selection) || count($selection) != 2 ||
                    ! is_numeric($selection[0]) || ! is_numeric($selection[1]) ||
                    (int) $selection[1] <= 0
                ) {
                    \Log::error('自選包 selectedItemIds 格式錯誤', ['selectedItemIds' => $selectedItemIds]);

                    return response()->json(ErrorService::errorCode(__METHOD__, 'INVENTORY:0005'), 422); // 格式錯誤
                }

                // 加總 "times"
                $totalTimes += (int) $selection[1];
            }

            // 總 "times" 必須等於 $amount
            if ($totalTimes != $amount) {
                // 總次數與開啟數量不符
                return response()->json(ErrorService::errorCode(__METHOD__, 'INVENTORY:0007'), 422);
            }
        }

        DB::beginTransaction();
        try {
            $result = $svc->openPackage($user, $itemId, $selectedItemIds, $amount);

            if ($result['success'] != 1) {
                DB::rollBack();

                return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
            }

            $rewards = $result['rewards'] ?? [];
            $finalRewards = [];

            foreach ($rewards as $reward) {
                $rewardItemId = $reward['item_id'];
                $rewardQty = $reward['qty'];

                $item = GddbItems::where('item_id', $rewardItemId)->first();
                if (! $item) {
                    throw new \Exception('禮包獎勵道具不存在: item_id = '.$rewardItemId);
                }

                if ($item->category === 'Equipment') {
                    // ===== 裝備邏輯修正 =====
                    $equipmentService = new SurgameEquipmentService;
                    for ($i = 0; $i < $rewardQty; $i++) {
                        // A. 給予真實裝備
                        $equipmentId = $equipmentService->giveEquipment($user->uid, $rewardItemId);
                        if (! $equipmentId) {
                            throw new \Exception('裝備發放失敗');
                        }

                        // B. 僅寫入 Log
                        UserItemLogs::changeQty(
                            UserItemLogs::TYPE_ITEM_PACKAGE,
                            $user->id,
                            0, // 裝備無 UserItem ID
                            $rewardItemId,
                            $item->manager_id,
                            0, 1,
                            '開啟禮包獲得裝備'
                        );
                    }
                    $finalRewards[] = ['item_id' => $rewardItemId, 'qty' => $rewardQty];

                } else {
                    // ===== 一般道具/英雄邏輯 =====
                    // addItem (包含英雄自動轉換)
                    $addResult = UserItemService::addItem(
                        UserItemLogs::TYPE_ITEM_PACKAGE,
                        $user->id,
                        $user->uid,
                        $rewardItemId,
                        $rewardQty,
                        1,
                        '開啟禮包獲得'
                    );

                    if ($addResult['success'] != 1) {
                        throw new \Exception('道具發放失敗');
                    }

                    // 檢查是否轉為英雄 ID (對應 addItem 的回傳)
                    // 如果 addItem 回傳了 'character_item_id' (轉碎片的狀況) 或是原本就是 Hero
                    $finalItemId = $addResult['character_item_id'] ?? $rewardItemId;
                    $finalQty = $addResult['character_qty'] ?? $rewardQty;

                    $finalRewards[] = [
                        'item_id' => $finalItemId,
                        'qty' => $finalQty,
                    ];
                }
            }

            DB::commit();

            return response()->json(['data' => $finalRewards], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('禮包開啟失敗', [
                'uid' => $user->uid,
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(ErrorService::errorCode(__METHOD__, 'INVENTORY:0005'), 422);
        }
    }

    private function formatUserInventory(Collection $items)
    {
        $newItems = $items->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'manager_id' => $item->manager_id,
                'qty' => (int) $item->qty,
                'type' => $item->item?->itemPackage?->use_necessary > 1 ? 'shard' : 'item',
            ];
        })->values()->all();

        $results = [];
        foreach ($newItems as $item) {
            if ($item['type'] == 'item') {
                $results[] = [
                    'item_id' => $item['item_id'],
                    'manager_id' => $item['manager_id'],
                    'qty' => $item['qty'],
                ];
            }
        }

        return $results;
    }

    private function formatUserEquipments(Collection $equipments)
    {
        return $equipments->map(fn ($eq) => [
            'equipment_uid' => $eq->id,
            'item_id' => $eq->item_id,
            'manager_id' => $eq->item?->manager_id,
            'deploy_index' => $this->getSlotIndexById($eq->uid, $eq->slot_id) ?? -1,
            'equip_index' => $eq->position ?? -1,
            'is_used' => (bool) $eq->is_used ? 1 : 0,
            'attributes' => json_decode($eq->attributes, true),
        ])->values()->all();
    }

    // 碎片
    private function formatUserShards(Collection $shards)
    {
        $newShards = $shards->map(function ($shard) {
            return [
                'item_id' => $shard->item_id,
                'manager_id' => $shard->manager_id,
                'qty' => (int) $shard->qty,
            ];
        })->values()->all();

        return $newShards;
    }

    // 完整背包回傳格式
    private function formatPackages($items, $equipments, $runes = [], $shard = [], $treasures = [])
    {
        $results = [];
        $results['items'] = $items;
        $results['shard'] = $shard;
        $results['equipments'] = $equipments;
        $results['treasures'] = $treasures;
        $results['runes'] = (object) $runes;

        return $results;
    }

    private function formatUserTreasure(Collection $treasures)
    {
        $newTreasures = $treasures->map(function ($treasure) {
            return [
                'item_id' => $treasure->item_id,
                'manager_id' => $treasure->manager_id,
                'qty' => (int) $treasure->qty,
            ];
        })->values()->all();

        return $newTreasures;
    }

    // 透過slot_id取得slot_index
    private function getSlotIndexById($uid, $slotId): ?int
    {
        $slot = CharacterDeploySlot::where(['uid' => $uid, 'id' => $slotId])->first();
        if ($slot) {
            return $slot->position;
        } else {
            return null;
        }
    }

    /**
     * 額外加入特定 item_id 到背包列表末尾（不需排序）
     *
     * @param  array  $items  已格式化的背包道具列表
     * @param  int  $userId  用戶 ID
     * @param  array  $specialItemIds  要額外加入的 item_id 陣列
     */
    private function appendSpecialItems(array $items, int $userId, array $specialItemIds): array
    {
        $existingItemIds = array_column($items, 'item_id');

        foreach ($specialItemIds as $itemId) {
            // 如果已經在列表中，跳過
            if (in_array($itemId, $existingItemIds)) {
                continue;
            }

            // 查詢該道具資訊
            $userItem = UserItems::with(['item'])
                ->where('user_id', $userId)
                ->where('item_id', $itemId)
                ->first();

            if ($userItem) {
                $items[] = [
                    'item_id' => $userItem->item_id,
                    'manager_id' => $userItem->manager_id,
                    'qty' => (int) $userItem->qty,
                ];
            }
        }

        return $items;
    }

    /**
     * 將前端給的selectedIds formatter
     */
    private function formatterSelectedIds($selectedItemIds): array
    {
        if (is_string($selectedItemIds) && ! empty($selectedItemIds)) {
            $jsonString = $selectedItemIds;
            if (str_contains($jsonString, '][')) {
                $jsonString = str_replace('][', '],[', $jsonString);
            }
            if (! str_starts_with($jsonString, '[[')) {
                $jsonString = '['.$jsonString.']';
            }
            $selectedItemIds = json_decode($jsonString, true);
        }
        if (! is_array($selectedItemIds)) {
            $selectedItemIds = [];
        }

        return $selectedItemIds;
    }
}
