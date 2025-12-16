<?php
namespace App\Service;

use App\Models\MapFavorite;
use App\Models\MapLike;
use App\Models\MapTag;
use App\Models\MaterialStage;
use App\Models\UserMaps;

class MapManageService
{
    public function getMapByType($user, $type)
    {
        switch ($type) {
            case 'draft':
                return UserMaps::with([
                    'user' => function ($query) {
                        $query->select('id', 'uid', 'name', 'draft_map_limit', 'map_limit');
                    },
                ])
                    ->where('user_id', $user->id)
                    ->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('is_publish', 0)
                                ->where('is_draft', 1);
                        })
                            ->orWhere('is_home', 1);
                    })
                    ->where('is_deleted', 0)
                    ->get();

            case 'published':
                return UserMaps::
                    where('user_id', $user->id)
                    ->where('is_publish', 1)
                    ->where('is_draft', 0)
                    ->where('is_home', 0)
                    ->where('is_deleted', 0)
                    ->get()
                    ->map(function ($map) {
                        $map->load_map_data = false;
                        return $map;
                    });
            case 'recycle':
                return UserMaps::
                    where('user_id', $user->id)
                    ->where('is_publish', 0)
                    ->where('is_draft', 0)
                    ->where('is_home', 0)
                    ->where('is_deleted', 1)
                    ->get()
                    ->map(function ($map) {
                        $map->load_map_data = false;
                        return $map;
                    });
            default:
                return null;
        }
    }
    // 創建草稿
    public function createDraft($user, $map_id)
    {
        $map = UserMaps::where('user_id', $user->id)->where('id', $map_id)->first();
        if (empty($map)) {
            return false;
        }
        $map->is_draft = 1;
        $map->save();
        return $map;
    }

    // 搜尋地圖
    public function filterPramas($userMaps, $selectType)
    {
        switch ($selectType) {
            case 1:
                return $userMaps->where('is_recommend', 1);
            case 2:
                return $userMaps->orderBy('created_at', 'desc');
            case 3:
                return $userMaps->where('play_count', '>', 0)->orderBy('play_count', 'desc');
            case 4:
                return $userMaps->whereHas('favorites', function ($query) {
                    $query->where('uid', auth()->guard('api')->user()->uid);
                });
            case 5:
                return $userMaps->whereHas('user.followers', function ($query) {
                    $query->where('follower_uid', auth()->guard('api')->user()->uid);
                });
            default:
                return $userMaps;
        }
    }
    // 回傳地圖資訊
    public function formatMapData($map, $type = 'normal')
    {
        $mapData = [
            'id'              => $map->id,
            'introduce'       => $map->introduce,
            'map_uuid'        => $map->map_uuid,
            'map_name'        => $map->map_name,
            'map_file_path'   => $map->map_file_path,
            'map_file_name'   => $map->map_file_name,
            'is_home'         => $map->is_home,
            'is_publish'      => $map->is_publish,
            'has_publish'     => $map->has_publish,
            'publish_at'      => $map->publish_at,
            'draft_id'        => (int) $map->draft_id,
            'map_type'        => (int) $map->map_type,
            'photo_file_path' => $map->photo_file_path,
            'updated_name'    => $map->updated_name,
            'is_draft'        => $map->is_draft,
            'is_deleted'      => $map->is_deleted,
            'map_data'        => $map->map_data,
            'photo_url'       => $map->photo_url,
            'play_count'      => $map->play_count,
            'created_at'      => $map->created_at,
            'updated_at'      => $map->updated_at,
            'map_tags'        => $this->tagIdToText($map->map_tags),
            'like_count'      => $this->checkLikeCount($map->id),
            'pass_rate'       => $this->calculateClearRate($map),
        ];

        if ($type == 'full') {
            $user                   = auth()->guard('api')->user();
            $mapData['is_favorite'] = $this->checkIsFavoriteOrLike($user, $map->id, 'favorite') ? 1 : 0;
            $mapData['is_like']     = $this->checkIsFavoriteOrLike($user, $map->id, 'like') ? 1 : 0;
            // unset map_data
            if (isset($mapData['map_data'])) {
                unset($mapData['map_data']);
            }
        }

        $mapData['user'] = [
            'id'   => $map->user->id,
            'uid'  => $map->user->uid,
            'name' => $map->user->name,
        ];

        return $mapData;
    }

    // 產生地圖編號
    public function generateMapUuid()
    {
        do {
            $mapUuid = rand(100000000000, 999999999999);
        } while (UserMaps::where('map_uuid', $mapUuid)->exists());
        return $mapUuid;
    }

    // 取得所有地圖標籤
    public function getAllMapTags()
    {
        return MapTag::get();
    }

    // 檢查是否為收藏或按讚地圖
    public function checkIsFavoriteOrLike($user, $map_id, $type)
    {
        if ($type == 'favorite') {
            return MapFavorite::where('uid', $user->uid)->where('map_id', $map_id)->exists();
        } else if ($type == 'like') {
            return MapLike::where('uid', $user->uid)->where('map_id', $map_id)->exists();
        }
        return false;
    }

    // 檢查被按讚的數量
    public function checkLikeCount($map_id)
    {
        return MapLike::where('map_id', $map_id)->count();
    }

    // tag id 轉 文字
    public function tagIdToText($tagIdsAry)
    {
        if (empty($tagIdsAry)) {
            return [];
        }

        if (is_string($tagIdsAry)) {
            $tagIds = array_values(json_decode($tagIdsAry, true));
        } else {
            $tagIds = $tagIdsAry;
        }
        return $tagIds;
    }

    // 地圖類型編號轉文字
    public function mapTypeIdToText($typeId)
    {
        $mapType = [
            1 => '闖關',
            2 => '競速',
            3 => '生存',
        ];

        return $mapType[$typeId] ?? '';
    }

    /** 地圖增加遊玩數 */
    public function increasePlayCount($map, int $count = 1)
    {
        try {
            if ($map) {
                $map->play_count += $count;
                $map->save();
            }
        } catch (Throwable $e) {
            \Log::error('增加地圖遊玩數失敗', [
                'message' => $e->getMessage(),
                'map_id'  => $map->id,
            ]);
        }
    }

    /** 地圖計數欄位增減處理 */
    public function adjustMapCounter($map, int $count = 1, string $field = 'like_count')
    {
        $map->$field += $count;
        if ($map->$field < 0) {
            $map->$field = 0;
        }

        $map->save();
    }

    /** 地圖增加通關數 */
    public function increaseClearCount($map, int $count = 1)
    {
        try {
            if ($map) {
                $map->pass_count += $count;
                $map->save();
            }
        } catch (Throwable $e) {
            \Log::error('增加地圖遊玩數失敗', [
                'message' => $e->getMessage(),
                'map_id'  => $map->id,
            ]);
        }
    }

    // 計算關卡通關率
    public function calculateClearRate($map)
    {
        if ($map->play_count == 0) {
            return 0;
        }
        return round($map->pass_count / $map->play_count, 2) * 100;
    }

    // 取得需要排除的地圖資訊
    public function getExcludeMapData(): array
    {
        return MaterialStage::get()->pluck('map_id')->toArray();
    }
}
