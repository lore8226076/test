<?php
namespace App\Service;

use App\Models\Blocklist;
use App\Models\Follows;
use App\Models\Users;
use Illuminate\Support\Facades\Log;

class FollowService
{

    // 取得我的粉絲 (追蹤我的)
    public function getUserFollowers(string $uid)
    {
        try {
            $data = Follows::where('following_uid', $uid)
                ->with('follower', function ($query) {
                    $query->select('uid', 'name', 'last_login_time', 'firebase_photo_url');
                })
                ->get()
                ->map(function ($item) use ($uid) {
                    $follower = $item->follower;
                    if ($follower) {
                        $follower->is_following = $this->isFollowing($uid, $follower->uid);
                    }
                    return $follower ? $follower->toArray() : null;
                })
                ->filter()
                ->values()
                ->toArray();
            return $data;
        } catch (\Exception $e) {
            Log::error('取得追蹤者失敗: ' . $e->getMessage());
            return [
                'success'    => 0,
                'error_code' => 'FOLLOW:0003',
            ];
        }
    }

    // 取得我追蹤的人
    public function getUserFollowings(string $uid)
    {
        try {
            $data = Follows::where('follower_uid', $uid)
                ->with('following', function ($query) {
                    $query->select('uid', 'name', 'last_login_time', 'firebase_photo_url');
                })
                ->get()
                ->map(function ($item) use ($uid) {
                    $following = $item->following;
                    if ($following) {
                        $following->is_fans = $this->isFollowing($following->uid, $uid);
                        $following->note    = $item->note;
                    }
                    return $following ? $following->toArray() : null;
                })
                ->filter()
                ->values()
                ->toArray();
            return $data;
        } catch (\Exception $e) {
            Log::error('取得我追蹤的人失敗: ' . $e->getMessage());
            return [
                'success'    => 0,
                'error_code' => 'FOLLOW:0004',
            ];
        }
    }

    // 追蹤
    public function follow(string $fromUid, string $toUid)
    {
        try {
            $follow = Follows::withTrashed()
                ->where('follower_uid', $fromUid)
                ->where('following_uid', $toUid)
                ->first();

            if ($follow) {
                if ($follow->trashed()) {
                    $follow->restore();
                }
                return [
                    'follower_uid'  => $fromUid,
                    'following_uid' => $toUid,
                ];
            }

            return Follows::create([
                'follower_uid'  => $fromUid,
                'following_uid' => $toUid,
            ]);
        } catch (\Exception $e) {
            Log::error('追蹤失敗: ' . $e->getMessage());
            return [
                'success'    => 0,
                'error_code' => 'FOLLOW:0001',
            ];
        }
    }

    // 取消追蹤
    public function unfollow(string $fromUid, string $toUid)
    {
        try {
            Follows::where('follower_uid', $fromUid)
                ->where('following_uid', $toUid)
                ->delete();
            return [
                'message' => '已成功取消追蹤',
            ];
        } catch (\Exception $e) {
            Log::error('取消追蹤失敗: ' . $e->getMessage());
            return [
                'success'    => 0,
                'error_code' => 'FOLLOW:0003',
            ];
        }
    }

    // 是否追蹤
    public function isFollowing(string $fromUid, string $toUid)
    {
        try {
            return Follows::where('follower_uid', $fromUid)
                ->where('following_uid', $toUid)
                ->exists();
        } catch (\Exception $e) {
            Log::error('是否追蹤失敗: ' . $e->getMessage());
            return [
                'success'    => 0,
                'error_code' => 'FOLLOW:0002',
            ];
        }
    }

    // 更新備註
    public function updateNote(string $fromUid, string $toUid, ?string $note)
    {
        try {
            $follow = Follows::where('follower_uid', $fromUid)
                ->where('following_uid', $toUid)
                ->first();

            if (! $follow) {
                return [
                    'success'    => 0,
                    'error_code' => 'FOLLOW:0004',
                ];
            }

            $follow->note = $note;
            $follow->save();

            return [
                'success' => 1,
                'message' => '備註更新成功',
            ];
        } catch (\Exception $e) {
            Log::error('更新追蹤備註失敗: ' . $e->getMessage());

            return [
                'success'    => 0,
                'error_code' => 'FOLLOW:0005', // 系統錯誤
            ];
        }
    }

    // 好友搜尋功能
    public function search(string $uid, string $keywords)
    {
        $exceptedUids = $this->getExceptedUids($uid);

        return Users::whereNotIn('uid', $exceptedUids)
            ->where(function ($query) use ($keywords) {
                $query->where('name', 'like', $keywords . '%')
                    ->orWhere('uid', 'like', $keywords . '%');
            })
            ->orderBy('last_login_time', 'desc')
            ->get(['uid', 'name', 'last_login_time', 'firebase_photo_url'])
            ->map(function ($user) use ($uid) {
                $user->is_fans = $this->isFollowing($user->uid, $uid);
                return $user;
            }) ?? [];
    }

    // 排除清單
    public function getExceptedUids(string $uid)
    {
        // 封鎖我的
        // $blockMeUids = Blocklist::where('blocked_uid', $uid)->pluck('uid');
        $blockMeUids = collect(array());
        // 我封鎖的
        $blockedUids = Blocklist::where('uid', $uid)->pluck('blocked_uid');
        // 我追蹤的
        $followedUids = Follows::where('follower_uid', $uid)->pluck('following_uid');
        // 自己的UID
        $myUid = [$uid];

        return $blockMeUids
            ->merge($blockedUids)
            ->merge($followedUids)
            ->merge($myUid)
            ->filter(function ($value) {
                return ! empty($value);
            })
            ->unique()
            ->values()
            ->toArray();
    }
}
