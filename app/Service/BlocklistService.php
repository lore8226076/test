<?php
namespace App\Service;

use App\Models\Blocklist;
use App\Models\Follows;
use Illuminate\Support\Facades\Log;

class BlocklistService
{
    // 取得我封鎖的人
    public function getBlockedUsers(string $uid)
    {
        try {
            return Blocklist::where('uid', $uid)
                ->with('blockedUser:uid,name,last_login_time')
                ->get()
                ->map(function ($item) {
                    return $item->blockedUser ? $item->blockedUser->toArray() : null;
                })
                ->filter()
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('取得封鎖名單失敗: ' . $e->getMessage());
            return [
                'success'    => 0,
                'error_code' => 'BLOCK:0003',
            ];
        }
    }

    // 取得封鎖我的人
    public function getBlockers(string $uid)
    {
        try {
            return Blocklist::where('uid', $uid)
                ->with('user:uid,name,last_login_time')
                ->get()
                ->map(function ($item) {
                    return $item->user ? $item->user->toArray() : null;
                })
                ->filter()
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('取得封鎖我的人失敗: ' . $e->getMessage());
            return [
                'success'    => 0,
                'error_code' => 'BLOCK:0002',
            ];
        }
    }

    // 封鎖使用者
    public function block(string $fromUid, string $toUid)
    {
        try {
            if ($fromUid === $toUid) {
                return [
                    'success'    => 0,
                    'error_code' => 'BLOCK:0001',
                ];
            }

            // 封鎖前，先解除雙向追蹤
            Follows::where(function ($q) use ($fromUid, $toUid) {
                $q->where('follower_uid', $fromUid)
                    ->where('following_uid', $toUid);
            })->orWhere(function ($q) use ($fromUid, $toUid) {
                $q->where('follower_uid', $toUid)
                    ->where('following_uid', $fromUid);
            })->delete();

            return Blocklist::updateOrCreate(
                ['uid' => $fromUid, 'blocked_uid' => $toUid],
                ['blocked_at' => now()]
            );
        } catch (\Exception $e) {
            Log::error('封鎖使用者失敗: ' . $e->getMessage());
            return [
                'success'    => 0,
                'error_code' => 'BLOCK:0003',
            ];
        }
    }

    // 解除封鎖
    public function unblock(string $fromUid, string $toUid)
    {
        try {
            Blocklist::where('uid', $fromUid)
                ->where('blocked_uid', $toUid)
                ->delete();
            return [
                'message' => '已成功解除封鎖',
            ];
        } catch (\Exception $e) {
            Log::error('解除封鎖失敗: ' . $e->getMessage());
            return [
                'success'    => 0,
                'error_code' => 'BLOCK:0004',
            ];
        }
    }

    // 是否已封鎖
    public static function isBlocked(string $fromUid, string $toUid): bool
    {
        return Blocklist::isBlocked($fromUid, $toUid);
    }
}
