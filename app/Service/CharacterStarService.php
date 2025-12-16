<?php
namespace App\Service;

use App\Models\GddbSurgameRankUps;

class CharacterStarService
{
    // 取得最高星級
    public static function getMaxStarLevel($groupId = 0)
    {
        $gddb = GddbSurgameRankUps::where('group_id', $groupId)->orderBy('star_level', 'desc')->first();
        return $gddb?->star_level ?? 0;
    }

    // 取得星級材料
    public static function getStarMaterial($groupId = 0, $starLevel = 1)
    {
        $gddb = GddbSurgameRankUps::where('group_id', $groupId)->where('star_level', $starLevel)->first();
        if (! $gddb) {
            return null;
        }

        return [
            'base_item_id'      => $gddb->base_item_id,
            'base_item_amount'  => $gddb->base_item_amount,
            'extra_item_id'     => $gddb->extra_item_id ?? 0,
            'extra_item_amount' => $gddb->extra_item_amount ?? 0,
        ];
    }

    // 檢查星級材料是否足夠
    public static function checkStarMaterial(int $groupId = 0, int $starLevel = 1, $user = null): bool
    {
        $userId = is_object($user) ? ($user->id ?? 0) : (int) $user;
        if ($userId < 0 || $groupId < 0 || $starLevel < 0) {
            return false;
        }

        $m = self::getStarMaterial($groupId, $starLevel);
        if (! $m) {
            return false;
        }

        $baseId    = (int) ($m['base_item_id'] ?? 0);
        $baseNeed  = (int) ($m['base_item_amount'] ?? 0);
        $extraId   = (int) ($m['extra_item_id'] ?? 0);
        $extraNeed = (int) ($m['extra_item_amount'] ?? 0);

        if ($baseId <= 0 || $baseNeed <= 0) {
            return false;
        }

        $svc = new UserItemService();

        // 1) 先檢查主資源
        $base = $svc->checkResource($userId, $baseId, $baseNeed);
        if (($base['success'] ?? 0) !== 1) {
            return false;
        }

        // 2) 再檢查額外資源
        if ($extraId > 0 && $extraNeed > 0) {
            if ($extraId !== $baseId) {
                // 不同道具：獨立再檢一次
                $extra = $svc->checkResource($userId, $extraId, $extraNeed);
                return ($extra['success'] ?? 0) === 1;
            } else {
                // 相同道具：以「剩餘量」避免各自檢查的誤判
                $qty = (int) UserItems::where('user_id', $userId)
                    ->where('item_id', $baseId)
                    ->value('qty');

                $remain = $qty - $baseNeed;
                return $remain >= $extraNeed;
            }
        }

        return true;
    }

}
