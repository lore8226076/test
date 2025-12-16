<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MapFavorite;
use App\Models\MapLike;
use App\Models\UserMaps;
use App\Models\UserSurGameInfo;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\FileService;
use App\Service\MapManageService;
use App\Service\TaskService;
use App\Service\UserService;
use App\Service\GradeTaskService;
use App\Service\UserStatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MapController extends Controller
{
    protected $mapManageService;

    public function __construct(Request $request, MapManageService $mapManageService)
    {
        $this->mapManageService = $mapManageService;
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['homeByUid', 'one', 'getAllMapTags']]);
        }
    }

    public function publish_list(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $user = auth()->guard('api')->user();

        // 取得需要排除的地圖資訊
        $excludeMapData = $this->mapManageService->getExcludeMapData();

        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        $data = $request->input();
        $keyword = $data['keyword'] ?? null;

        $responseData = [];

        // today_pick
        if (isset($data['today_pick']) && $data['today_pick'] == 1) {
            $query = UserMaps::where('is_publish', 1)->where('is_featured', 1)
                ->whereNotIn('id', $excludeMapData)
                ->whereHas('user', fn ($q) => $q->where('is_active', 1));
            if ($keyword) {
                $query->where('map_name', 'like', '%'.$keyword.'%');
            }

            // 查詢結果
            $pagination = $query->with(['user:id,uid,name'])
                ->orderBy('publish_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // 把每筆都隱藏掉 map_data
            $pagination->getCollection()->transform(function ($item) {
                $item->load_map_data = false;

                return $this->mapManageService->formatMapData($item, 'full');
            });

            // 存到 response
            $responseData['today_pick'] = [
                'last_page' => $pagination->lastPage(),
                'current_page' => $pagination->currentPage(),
                'data' => $pagination->items(),
            ];
        }

        // my_follow
        if (isset($data['my_follow']) && $data['my_follow'] == 1) {
            $query = UserMaps::where('is_publish', 1)
                ->whereNotIn('id', $excludeMapData)
                ->whereHas('user', fn ($q) => $q->whereIn('uid', $user->following()->pluck('following_uid'))->where('is_active', 1));

            if ($keyword) {
                $query->where('map_name', 'like', '%'.$keyword.'%');
            }

            // 查詢結果
            $pagination = $query->with(['user:id,uid,name'])
                ->where('is_publish', 1)
                ->orderBy('publish_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // 把每筆都隱藏掉 map_data
            $pagination->getCollection()->transform(function ($item) {
                $item->load_map_data = false;

                return $this->mapManageService->formatMapData($item, 'full');
            });

            // 存到 response
            $responseData['my_follows'] = [
                'last_page' => $pagination->lastPage(),
                'current_page' => $pagination->currentPage(),
                'data' => $pagination->items(),
            ];
        }

        // parimary_map_type
        if (isset($data['parimary_map_type'])) {
            $query = UserMaps::where('is_publish', 1)
                ->where('map_type', $data['parimary_map_type'])
                ->whereNotIn('id', $excludeMapData)
                ->whereHas('user', fn ($q) => $q->where('is_active', 1));
            // 次要類型
            if (isset($data['secondary_map_type'])) {
                $query = $this->mapManageService->filterPramas($query, $data['secondary_map_type']);
            }
            if (isset($data['tag_id'])) {
                $query->whereNotNull('map_tags')->where('map_tags', 'like', '%'.$data['tag_id'].'%');
            }

            if ($keyword) {
                $query->where('map_name', 'like', '%'.$keyword.'%');
            }

            // 查詢結果
            $pagination = $query->with(['user:id,uid,name'])
                ->where('is_publish', 1)
                ->orderBy('publish_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // 把每筆都隱藏掉 map_data
            $pagination->getCollection()->transform(function ($item) {
                $item->load_map_data = false;

                return $this->mapManageService->formatMapData($item, 'full');
            });

            // 存到 response
            $responseData['parimary_map_type'] = [
                'last_page' => $pagination->lastPage(),
                'current_page' => $pagination->currentPage(),
                'data' => $pagination->items(),
            ];
        }

        // 如果都沒指定，就回傳全部
        if (empty($responseData)) {
            $query = UserMaps::whereNotIn('id', $excludeMapData)
                ->where('is_publish', 1)
                ->whereHas('user', fn ($q) => $q->where('is_active', 1));

            if ($keyword) {
                $query->where('map_name', 'like', '%'.$keyword.'%');
            }

            // 查詢結果
            $pagination = $query->with(['user:id,uid,name'])
                ->orderBy('publish_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // 把每筆都隱藏掉 map_data
            $pagination->getCollection()->transform(function ($item) {
                $item->load_map_data = false;

                return $this->mapManageService->formatMapData($item, 'full');
            });

            // 存到 response
            $responseData['all'] = [
                'last_page' => $pagination->lastPage(),
                'current_page' => $pagination->currentPage(),
                'data' => $pagination->items(),
            ];
        }

        return response()->json(['data' => $responseData], 200);
    }

    /** 家園地圖列表 */
    public function home_list(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $userMaps = UserMaps::with(['user' => function ($query) {
            $query->select('id', 'uid', 'name');
        }])
            ->where('is_home', 1)
            ->whereHas('user', function ($query) {
                return $query->where('is_active', 1)->whereNotNull('name');
            });

        $data = $request->input();
        foreach ($data as $field => $value) {
            $field = str_replace('__', '.', $field);
            switch ($field) {
                case 'per_page':
                case 'to_key_value':
                case 'current_page':
                case 'sort':
                case 'direction':
                case '_token':
                    break;
                default:
                    $userMaps = $userMaps->where($field, $value);
                    break;
            }
        }

        if (empty($data['sort'])) {
            $data['sort'] = 'publish_at';
        }
        $sortField = $data['sort'];
        $sortDirection = ! empty($data['direction']) ? $data['direction'] : 'desc';
        $userMaps = $userMaps->orderBy($sortField, $sortDirection);

        if ($perPage == 0) {
            $userMaps = $userMaps->get();
        } else {
            $current_page = empty($data['current_page']) ? 1 : $data['current_page'];
            $userMaps = $userMaps->paginate($perPage, ['*'], 'page', $current_page);
        }

        if (isset($data['to_key_value'])) {
            $to_key_value = explode('-', $data['to_key_value']);
            $temp = [];
            foreach ($userMaps as $userMap) {
                $temp[$userMap->{$to_key_value[0]}] = $userMap->{$to_key_value[1]};
            }
            $userMaps = $temp;
        }

        return response()->json(['data' => $userMaps], 200);
    }

    /** 取得家園地圖 */
    public function homeByUid(Request $request, $uid)
    {
        $data = $request->input();

        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $user = Users::where('uid', $uid)->first();

        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $userMap = UserService::getHomeMap($user->id);
        if (! $userMap) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 404);
        }

        return response()->json(['data' => $userMap], 200);
    }

    /** 第一次上傳草稿資料 */
    public function upload(Request $request)
    {
        $data = $request->input();
        if (empty($data['map_data'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0001'), 422);
        }

        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }
        // 檢查草稿地圖數量是否超過限制
        if (! $this->checkDraftCount($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0002'), 401);
        }

        $userMap = new UserMaps;
        $userMap->user_id = $user->id;
        $userMap->is_draft = true; // 草稿
        $userMap->map_uuid = $this->mapManageService->generateMapUuid();
        $userMap->introduce = $data['introduce'] ?? null;
        $userMap->map_name = $data['map_name'] ?? null;
        if (empty($data['map_data'])) {
            $data['map_data'] = '{"mapname":"test","start_x":1,"start_y":2,"start_z":3,"floor":[{"x":1,"y":2}]}';
        }

        // 儲存地圖資料
        $this->saveMapData($user, $userMap, $data['map_data']);
        $userMap->save();
        // ============ 任務系統 ============
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);
        // ============ 任務系統 ============

        $userMap = $this->mapManageService->formatMapData($userMap);

        return response()->json(['data' => $userMap, 'finishedTask' => $formattedTaskResult], 200);
    }

    /** 草稿更新資料 */
    public function update(Request $request, $id)
    {
        $data = $request->input();

        $user = auth()->guard('api')->user();
        if (empty($user)) {
            \Log::warning('使用者未登入，無法更新地圖草稿');

            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        $userMap = UserMaps::find($id);
        if (empty($userMap) || $userMap->user_id != $user->id || $userMap->is_draft == false) {
            \Log::warning('地圖草稿不存在或無權限', [
                'map_id' => $id,
                'user_id' => $user->id,
            ]);

            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }

        // map_data 儲存成檔案
        if (array_key_exists('map_data', $data) && $userMap->map_data !== $data['map_data']) {
            $this->saveMapData($user, $userMap, $data['map_data']);
        }

        // 已有家園, 且不是當前草稿, 提示
        if (isset($data['is_home']) && $data['is_home'] == 1) {
            $homeMap = UserService::getHomeMap($user->id);
            // 如果是不同地圖, 將當前$userMap設為家園
            if ($homeMap && $homeMap->id !== $userMap->id) {
                $homeMap->is_home = false;
                $homeMap->save();

                $userMap->is_home = true;
                $userMap->save();
            }
        }

        // 如果有標籤
        if (isset($data['tag_ids'])) {
            $data['map_tags'] = $data['tag_ids'];
            unset($data['tag_ids']);
        }

        $userMap->fill($data);

        $saveStatus = false;
        try {
            $userMap->save();
            $saveStatus = true;
            // ============ 任務系統 ============
            $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
            $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);
            // ============ 任務系統 ============
        } catch (Throwable $e) {
            \Log::error('草稿更新資料失敗', [
                'message' => $e->getMessage(),
                'data' => $userMap,
            ]);
        }
        if ($saveStatus) {
            $userMap = $this->mapManageService->formatMapData($userMap);

            return response()->json(['data' => $userMap], 200);
        } else {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0003'), 500);
        }
    }

    /** 發布地圖or更新發布地圖資料 */
    public function publishOrUpdate(Request $request, $draft_id)
    {
        $data = $request->input();

        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }
        $userMap = UserMaps::find($draft_id);
        if (empty($userMap) || $userMap->user_id != $user->id) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }

        if ($userMap->has_publish) {
            // 取得更新資料
            $publishMap = UserMaps::where('user_id', $user->id)->where('is_publish', 1)->where('draft_id', $draft_id)->first();
            if (empty($publishMap)) {
                $publishMap = $userMap->replicate();
                $publishMap->is_publish = true;
                $publishMap->has_publish = false;
                $publishMap->is_home = false; // 新地圖只給發布使用
                $publishMap->draft_id = $draft_id;
                $publishMap->publish_at = Carbon::now()->format('Y-m-d H:i:s');
                $publishMap->fill($data);
                // 重新儲存地圖資料
                $this->saveMapData($user, $publishMap, $userMap->map_data);
                $publishMap->save();
            }

            // 可繼承欄位
            $canInheritFields = ['map_name', 'map_file_path', 'map_file_name', 'introduce', 'photo_file_path', 'updated_name', 'map_type', 'map_tags'];
            foreach ($canInheritFields as $field) {
                $publishMap->$field = $userMap->$field;
            }
            // 繼承欄位後, 更新其他資訊
            $publishMap->fill($data);
            // 如果publish_at為空, 則設為當前時間
            if (empty($publishMap->publish_at)) {
                $publishMap->publish_at = Carbon::now()->format('Y-m-d H:i:s');
            }
            if (is_numeric($publishMap->publish_at)) {
                $publishMap->publish_at = Carbon::createFromTimestamp($publishMap->publish_at);
            }

            // 避免資料設定錯誤
            $publishMap->is_publish = true;
            $publishMap->is_draft = false;
            $publishMap->is_home = false;
            // 重新儲存地圖資料
            $this->saveMapData($user, $publishMap, $userMap->map_data);
            $publishMap->save();
            $publishMap = $this->mapManageService->formatMapData($publishMap);

            return response()->json(['data' => $publishMap], 200);
        }

        // 檢查發布地圖是否超過數量
        if (! $this->checkDraftCount($user, 'map_limit')) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0002'), 401);
        }
        $publishMap = $userMap->replicate();
        $publishMap->is_publish = true;
        $publishMap->has_publish = false;
        $publishMap->is_draft = false;
        $publishMap->is_home = false; // 新地圖只給發布使用
        $publishMap->draft_id = $draft_id;
        $publishMap->publish_at = Carbon::now()->format('Y-m-d H:i:s');
        $publishMap->fill($data);
        // 重新儲存地圖資料
        $this->saveMapData($user, $publishMap, $userMap->map_data);
        $publishMap->save();
        // 原始草稿設為已發布
        $userMap->has_publish = true;
        $userMap->save();

        // ============ 任務系統 ============
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);
        // ============ 任務系統 ============

        return response()->json(['data' => $publishMap, 'finishedTask' => $formattedTaskResult], 200);
    }

    /** 編輯發布中的地圖 */
    public function editPublishedMap(Request $request, $map_id)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }
        $userMap = UserMaps::find($map_id);
        if (empty($userMap) || $userMap->is_draft) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0001'), 401);
        }
        $data = $request->input();

        // introduce
        if (isset($data['introduce'])) {
            $data['introduce'] = $data['introduce'];
        }
        if (isset($data['map_name'])) {
            $data['map_name'] = $data['map_name'];
        }
        if (isset($data['tag_ids'])) {
            $data['map_tags'] = $data['tag_ids'];
            unset($data['tag_ids']);
        }

        $userMap->fill($data);
        $userMap->save();

        $userMap = $this->mapManageService->formatMapData($userMap);
        // ============ 任務系統 ============
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);
        // ============ 任務系統 ============

        return response()->json(['data' => $userMap, 'finishedTask' => $formattedTaskResult], 200);
    }

    /** 設為家園(暫無使用) */
    public function setHomeMap(Request $request, $map_id)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        // 檢查是否有家園
        if (UserMaps::where('user_id', $user->id)->where('is_home', 1)->exists()) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0007'), 401);
        }

        $userMap = UserMaps::where('user_id', $user->id)
            ->where('id', $map_id)
            ->where('is_home', 0)
            ->where('is_deleted', 0)
            ->where('is_draft', 0)
            ->first();

        if (empty($userMap)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0001'), 401);
        }

        $userMap->is_home = true;
        $userMap->save();

        return response()->json(['data' => $userMap], 200);
    }

    /** 放進回收區 */
    public function recycleMap(Request $request, $map_id)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }
        $map = UserMaps::where('user_id', $user->id)
            ->where('id', $map_id)
            ->where('has_publish', 0)
            ->where('is_deleted', 0)
            ->where('is_draft', 1)
            ->first();

        if (! $map) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0001'), 401);
        }

        $map->is_publish = 0;
        $map->is_home = 0;
        $map->is_deleted = 1;
        $map->is_draft = 0;
        $map->save();

        return response()->json(['data' => $map], 200);
    }

    /** 下架地圖 */
    public function unpublishMap(Request $request, $map_id)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }
        $map = UserMaps::where('user_id', $user->id)
            ->where('id', $map_id)
            ->where('is_publish', 1)
            ->first();
        if (empty($map)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }

        // 草稿地圖要先把has_publish設為false
        $draftMap = UserMaps::where('user_id', $user->id)
            ->with('draft')
            ->where('id', $map_id)
            ->first();

        if (! empty($draftMap)) {
            if ($draftMap->draft) {
                $draftMap = $draftMap->draft;
            }
            $draftMap->has_publish = false;
            $draftMap->save();
        }

        $result = $map->forceDelete();
        if (! $result) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 401);
        }

        return response()->json(['message' => '地圖已成功下架'], 200);
    }

    /** 地圖列表 */
    public function list(Request $request)
    {
        $data = $request->input();
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        $type = $data['type'] ?? 'published';

        // type: published, draft, recycle
        $results = $this->mapManageService->getMapByType($user, $type);

        if ($results->count() !== 0) {
            $results = $results->map(function ($result) use ($type) {
                if ($type == 'published') {
                    return $this->mapManageService->formatMapData($result, 'full');
                } else {
                    return $this->mapManageService->formatMapData($result);
                }
            });
        }

        return response()->json(['data' => ['userMaps' => $results]], 200);
    }

    /** 取得地圖資訊 */
    public function one(Request $request, $id)
    {

        $userMap = UserMaps::with(['user' => function ($query) {
            $query->select('id', 'uid', 'name');
        }])->find($id);

        if (empty($userMap)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 401);
        }
        $userMap = $this->mapManageService->formatMapData($userMap);

        return response()->json(['data' => ['userMap' => $userMap]], 200);
    }

    /** 刪除地圖 */
    public function destroy($map_id)
    {
        if (empty($map_id)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }

        try {
            $user = auth()->guard('api')->user();
            if (empty($user)) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
            }

            if (UserMaps::where('user_id', $user->id)->count() <= 1) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0005'), 401);
            }

            $userMap = UserMaps::find($map_id);

            if (empty($userMap) || $userMap->user_id != $user->id) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
            }

            if ($userMap->is_home) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0006'), 401);
            }

            if (UserMaps::where('user_id', $user->id)
                ->where('id', $map_id)
                ->where(function ($query) {
                    $query->where('is_publish', 1)
                        ->orWhere('is_home', 1);
                })
                ->exists()) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0007'), 401);
            }

            $userMap->forceDelete();
        } catch (Throwable $e) {
            // 刪除失敗
            \Log::error('刪除地圖失敗', [
                'message' => $e->getMessage(),
                'data' => $userMap,
            ]);

            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 401);
        }

        return response()->json(['data' => ['message' => '地圖已成功刪除']], 200);
    }

    /** 回收返回草稿 */
    public function recycleToDraft(Request $request, $map_id)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }
        $userMap = UserMaps::find($map_id);
        if (empty($userMap) || $userMap->user_id != $user->id) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }
        $userMap->is_draft = true;
        $userMap->is_deleted = false;
        $userMap->save();
        $userMap = $this->mapManageService->formatMapData($userMap);

        return response()->json(['data' => $userMap], 200);
    }

    /** 複製草稿地圖 */
    public function copyDraftMap(Request $request, $draft_id)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        // 檢查草稿數量
        if (! $this->checkDraftCount($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0002'), 401);
        }

        $userMap = UserMaps::find($draft_id);
        if (empty($userMap) || $userMap->user_id != $user->id || $userMap->is_draft == false) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }

        $newMap = $userMap->replicate();
        $newMap->is_draft = true;
        $newMap->is_home = false;
        $newMap->is_publish = false;
        $newMap->has_publish = false;
        $newMap->is_deleted = false;
        $newMap->is_publish = false;
        // 存新的地圖檔
        $this->saveMapData($user, $newMap, $userMap->map_data);
        $newMap->map_uuid = $this->mapManageService->generateMapUuid();
        $newMap->save();
        $newMap = $this->mapManageService->formatMapData($newMap);

        return response()->json(['data' => $newMap], 200);
    }

    /** 更新地圖照片 */
    public function updateMapPhoto(Request $request, $map_id)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        if (empty($map_id)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }

        $userMap = UserMaps::find($map_id);
        if (empty($userMap) || $userMap->user_id != $user->id) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }

        // 上傳新圖片
        $data = $request->input();
        if ($request->hasFile('new_photo')) {
            $result = FileService::upload_file($request->file('new_photo'), 'map_photo', $userMap->id);
            if ($result) {
                $userMap->photo_file_path = $result['file_path'].$result['file_name'];
            }
        } elseif (isset($data['new_photo'])) {
            $result = FileService::upload($data['new_photo'], 'map_photo', $userMap->id);
            if ($result) {
                $userMap->photo_file_path = $result['file_path'].$result['file_name'];
            }
        }

        $userMap->save();

        $userMap = $this->mapManageService->formatMapData($userMap);

        // ============ 任務系統 ============
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);
        // ============ 任務系統 ============

        return response()->json(['data' => $userMap, 'finishedTask' => $formattedTaskResult], 200);
    }

    /** map_data 儲存成檔案 */
    private function saveMapData($user, $userMap, $map_data)
    {
        // 取得r2路徑
        if (! empty($userMap->map_file_path)) {
            FileService::deleteR2File($userMap->map_file_path);
        }

        $txtContent = $map_data;
        $module = 'user_maps';
        $file_name = $user->id.'_'.time().rand(1000, 9999).'.txt';
        $result = FileService::upload_string($txtContent, $module, $file_name);
        if (! $result) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0004'), 500);
        }
        $userMap->map_file_path = $result['file_path'];
        $userMap->map_file_name = $result['file_name'];
    }

    /** 檢查草稿數量 */
    private function checkDraftCount($user, $limitCol = 'draft_map_limit')
    {
        $draftCount = UserMaps::where('user_id', $user->id)->where('is_draft', 1)->count();
        if ($draftCount >= $user->$limitCol) {
            return false;
        }

        return true;
    }

    /** 取得所有地圖標籤 */
    public function getAllMapTags()
    {
        $tags = $this->mapManageService->getAllMapTags();

        return response()->json(['data' => $tags], 200);
    }

    /** 按讚地圖 */
    public function likeMap(Request $request, $map_id)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        $map = UserMaps::find($map_id);
        if (empty($map)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }

        $mapLike = MapLike::where('uid', $user->uid)->where('map_id', $map_id)->first();
        if (empty($mapLike)) {
            $mapLike = new MapLike;
            $mapLike->uid = $user->uid;
            $mapLike->map_id = $map_id;
            $mapLike->save();

            $this->mapManageService->adjustMapCounter($map, 1, 'like_count');
        } else {
            $mapLike->delete();
            $this->mapManageService->adjustMapCounter($map, -1, 'like_count');
        }

        return response()->json(['data' => ['message' => '地圖已成功按讚']], 200);
    }

    /** 收藏地圖 */
    public function favoriteMap(Request $request, $map_id)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        $map = UserMaps::find($map_id);
        if (empty($map)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0003'), 401);
        }

        $mapFavorite = MapFavorite::where('uid', $user->uid)->where('map_id', $map_id)->first();
        if (empty($mapFavorite)) {
            $mapFavorite = new MapFavorite;
            $mapFavorite->uid = $user->uid;
            $mapFavorite->map_id = $map_id;
            $mapFavorite->save();
            $this->mapManageService->adjustMapCounter($map, 1, 'favorite_count');
        } else {
            $mapFavorite->delete();
            $this->mapManageService->adjustMapCounter($map, -1, 'favorite_count');
        }

        return response()->json(['data' => ['message' => '地圖已成功收藏']], 200);
    }

    /** 地圖標籤列表 */
    public function mapTagsList(Request $request)
    {
        $mapTags = $this->mapManageService->getMapTagsList();
        if ($mapTags->isEmpty()) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0008'), 404);
        }
        $tags = $mapTags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'icon' => $tag->icon,
            ];
        });

        return response()->json(['data' => $tags], 200);
    }

    // 玩家任務處理
    private function handleUserTasks($user, $userSurgameInfo)
    {
        // ============ 任務系統 ============
        $taskService = new TaskService;
        $userStatsService = new UserStatsService($taskService);
        $taskStatsService = new UserStatsService($taskService, $taskService->keywords(), [$taskService, 'calculateStat']);
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $taskService->autoAssignTasks($user->uid);
        $gradeSerivce = new GradeTaskService;
        $gradeSerivce->autoAsignGradeTask($userSurgameInfo);
        $taskStatsService->updateByKeyword($user, 'home');
        $completedTask = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);

        // ============ 任務系統 ============
        return $formattedTaskResult;
    }
}
