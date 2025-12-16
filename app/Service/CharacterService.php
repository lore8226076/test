<?php

namespace App\Service;

use App\Models\GddbSurgameHeroes as Heros;
use App\Models\GddbSurgamePlayerLvUp as PlayerLvUp;
use App\Models\UserCharacter;
use App\Models\UserItemLogs;
use App\Models\UserItems;
use App\Models\UserSurGameInfo as UserInfo;
use DB;

class CharacterService
{
    public $weightLists = [
        'R' => 7,
        'SR' => 2,
        'SSR' => 1,
    ];

    private const LEVEL_UP_ITEM_ID = 190; // 主角升級所需的道具ID

    // public function obtainCharacter()
    // {
    //     // 取出英雄資料（id、unique_id、rarity）
    //     $heros = Heros::where('unique_id', '>', 1000)
    //         ->get(['id', 'unique_id', 'rarity']);

    //     foreach ($heros as $hero) {
    //         $hero->weight = $this->weightLists[$hero->rarity] ?? 0;
    //     }

    //     // 計算總權重
    //     $totalWeight = $heros->sum('weight');

    //     if ($totalWeight <= 0) {
    //         return null;
    //     }

    //     // 抽一個隨機數
    //     $rand = rand(1, $totalWeight);

    //     // 選出角色
    //     $current = 0;
    //     foreach ($heros as $hero) {
    //         $current += $hero->weight;
    //         if ($rand <= $current) {
    //             return $hero;
    //         }
    //     }

    //     return null;
    // }

    // 主角等級同步
    public static function syncMainCharacter($user)
    {
        $currentUser = UserInfo::where('uid', $user->uid)->first();
        if (empty($currentUser)) {
            return [
                'success' => false,
                'error_code' => 'AUTH:0001',
            ];
        }

        $playerLvUp = PlayerLvUp::where('account_lv', $currentUser->main_character_level + 1)->first();
        if (empty($playerLvUp)) {
            return [
                'success' => false,
                'error_code' => 'PlayerLevelUp:0001',
            ];
        }

        // 1. 檢查主角身上道具
        $itemCheck = self::canUpgrade($user);
        if ($itemCheck['success'] == 0) {
            return [
                'success' => false,
                'error_code' => $itemCheck['error_code'],
            ];
        }
        // 2. 進行角色升級
        $currentUserInfo = UserInfo::with('user')->where('uid', $currentUser->uid)->first();
        if (! $currentUserInfo) {
            return [
                'success' => false,
                'error_code' => 'AUTH:0001',
            ];
        }
        $currentUserInfo->main_character_level += 1;
        $currentUserInfo->save();
        // 取得升級獎勵
        $reward = $playerLvUp->reward ?? [];
        if (empty($reward)) {
            return [
                'success' => true,
                'message' => '升級成功，無獎勵',
            ];
        }

        // 獎勵為[item_id, amount]格式
        if (is_string($reward)) {
            $reward = json_decode($reward, true);
        }

        // 檢查獎勵是否為陣列
        if (! is_array($reward)) {
            $reward = [];
        }

        if (is_array($reward) && count($reward) == 2) {
            $reward = [
                'item_id' => $reward[0],
                'amount' => $reward[1],
            ];
        }
        if (! empty($reward)) {
            $result = UserItemService::addItem(50, $currentUserInfo->user->id, $currentUserInfo->uid, $reward['item_id'], $reward['amount'], 1, '主角升級獎勵');
            if ($result['success'] == 0) {
                return [
                    'success' => false,
                    'error_code' => $result['error_code'],
                ];
            }
        }

        return [
            'success' => true,
            'reward' => $reward,
        ];
    }

    // 檢查是否能夠升級
    public static function canUpgrade($user)
    {
        $item = UserItems::where('user_id', $user->id)->where('item_id', self::LEVEL_UP_ITEM_ID)->first();
        if (empty($item)) {
            $addNewItem = UserItemService::addItem(
                1,
                $user->id,
                $user->uid,
                self::LEVEL_UP_ITEM_ID,
                100,
                1,
                '初始升級道具');
            if ($addNewItem['success'] == 0) {
                return ['success' => 0, 'error_code' => $addNewItem['error_code']];
            }
            $item = UserItems::where('user_id', $user->id)->where('item_id', self::LEVEL_UP_ITEM_ID)->first();

        }
        $nextLevel = $user?->surgameUserInfo?->main_character_level + 1 ?? null;
        if (empty($nextLevel)) {
            return ['success' => 0, 'error_code' => 'PlayerLevelUp:0003'];
        }

        $required_amount = PlayerLvUp::where('account_lv', $nextLevel)->value('xp');
        if (empty($required_amount)) {
            return ['success' => 0, 'error_code' => 'PlayerLevelUp:0001'];
        }
        // 檢查道具數量是否足夠
        if (empty($item) || $item->qty < $required_amount) {
            return ['success' => 0, 'error_code' => 'UserItem:0003'];
        }

        return ['success' => 1, 'error_code' => ''];
    }

    // 取得使用者角色列表
    public static function getUserCharacterList($uid)
    {
        $deployCharAry = UserCharacter::where('uid', $uid)
            ->where('has_use', 1)
            ->orderByRaw('CAST(slot_index AS UNSIGNED)')
            ->get(['character_id', 'star_level', 'slot_index'])
            ->map(fn ($c) => [
                'slot_index' => (int) $c->slot_index,
                'character_id' => (int) $c->character_id,
                'star_level' => (int) $c->star_level,
            ])
            ->toArray();

        $undeployCharAry = UserCharacter::where('uid', $uid)
            ->where('has_use', 0)
            ->join('gddb_surgame_heroes', 'user_characters.character_id', '=', 'gddb_surgame_heroes.character_id')
            ->get(['user_characters.character_id', 'user_characters.star_level', 'gddb_surgame_heroes.rarity'])
            ->sort(function ($a, $b) {
                // 定義稀有度排序 SSR > SR > R
                $rarityOrder = ['SSR' => 3, 'SR' => 2, 'R' => 1];
                $rarityA = $rarityOrder[$a->rarity] ?? 0;
                $rarityB = $rarityOrder[$b->rarity] ?? 0;
                if ($rarityA === $rarityB) {
                    // 稀有度相同時，依 star_level 排序（高到低）
                    return $b->star_level <=> $a->star_level;
                }

                // 稀有度排序（高到低）
                return $rarityB <=> $rarityA;
            })
            ->map(fn ($c) => [
                'character_id' => $c->character_id,
                'star_level' => $c->star_level,
                'rarity' => $c->rarity,
            ])
            ->values()
            ->toArray();

        return [
            'deploy' => $deployCharAry,
            'undeploy' => $undeployCharAry,
        ];

    }

    /**
     * 獲得英雄（共用方法）
     * 如果已擁有則轉換成碎片，未擁有則建立新英雄
     *
     * @param  int  $userId  用戶 ID
     * @param  int  $uid  用戶 UID
     * @param  int  $characterId  英雄 ID
     * @param  string  $memo  日誌備註（選填）
     * @return array
     */
    public static function obtainCharacter($userId, $uid, $characterId, $memo = '獲得英雄')
    {
        try {
            DB::beginTransaction();

            // 1. 獲得英雄
            $result = UserCharacter::tryObtain($uid, $characterId);

            $reward = null;

            // 2. 判斷結果
            if ($result['status'] === 'exists') {
                // 已擁有，需要轉成碎片
                $fragmentId = $result['fragment_id'];

                $itemResult = UserItemService::addItem(
                    UserItemLogs::TYPE_CHARACTER_FRAGMENT,
                    $userId,
                    $uid,
                    $fragmentId,
                    1,
                    1,
                    "{$memo} - 重複轉換碎片"
                );

                if ($itemResult['success'] != 1) {
                    DB::rollBack();

                    return $itemResult;
                }

                $reward = ['item_id' => $fragmentId, 'amount' => 1];
            }

            DB::commit();

            return [
                'success' => true,
                'character' => $result['character'],
                'already_has' => ($result['status'] === 'exists'),
                'reward' => $reward,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('獲得英雄失敗', [
                'user_id' => $userId,
                'uid' => $uid,
                'character_id' => $characterId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error_code' => 'CHARACTER:0015',
                'message' => '獲得英雄失敗',
            ];
        }
    }

    /**
     * 檢查使用者是否擁有該角色，並計算碎片數量
     *
     * @param  int  $uid  用戶 UID
     * @param  int  $characterId  英雄 ID
     * @return array
     */
    public static function checkCharacterOrFragment($uid, $characterId)
    {
        // 檢查玩家是否已擁有該英雄
        $alreadyHas = UserCharacter::where('uid', $uid)
            ->where('character_id', $characterId)
            ->exists();

        if ($alreadyHas) {
            // 已擁有，轉換成碎片
            $fragmentId = self::getHeroFragmentId($characterId);

            return [
                'already_has' => true,
                'is_fragment' => true,
                'item_id' => $fragmentId,
                'amount' => 1,
            ];
        }

        // 未擁有，給予新英雄
        return [
            'already_has' => false,
            'is_fragment' => false,
            'item_id' => $characterId,
            'amount' => 1,
        ];
    }

    /** 取得碎片id */
    public static function getHeroFragmentId($characterId)
    {
        return Heros::getFragmentIdByCharacterId($characterId);
    }

    /** 透過item_id轉換成character_id */
    public static function getCharacterIdByItemId($itemId)
    {
        return Heros::getCharacterIdByItemId($itemId);
    }
}
