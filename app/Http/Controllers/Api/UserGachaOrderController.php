<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gachas;
use App\Models\UserGachaOrderDetails;
use App\Models\UserGachaOrders;
use App\Models\Users;
use App\Models\UserStats;
use App\Models\UserSurGameInfo;
use App\Service\ErrorService;
use App\Service\GradeTaskService;
use App\Service\TaskService;
use App\Service\UserGachaOrderService;
use App\Service\UserItemService;
use App\Service\UserStatsService;
use Illuminate\Http\Request;

class UserGachaOrderController extends Controller
{
    // 卡池類型常數
    const TYPE_CHARACTER = 1;  // 英雄卡池

    const TYPE_TREASURE = 2;   // 寶物卡池

    const TYPE_AVATAR = 3;     // 紙娃娃與家具扭蛋

    const TYPE_EVENT = 4;      // 活動卡池

    public function __construct(Request $request)
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => []]);
        }
    }

    /**
     * 統一抽取扭蛋入口
     * type = 1: 英雄卡池
     * type = 2: 寶物卡池
     * type = 3: 紙娃娃與家具扭蛋
     * type = 4: 活動卡池
     */
    public function create(Request $request)
    {
        $data = $request->input();

        // 驗證用戶
        $user = Users::find(auth()->guard('api')->user()->id);
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        // 驗證 gacha_id
        if (empty($data['gacha_id'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'GachaOrder:0001'), 422);
        }

        // 取得卡池資料
        $gacha = Gachas::with('gachaDetails.itemDetail')->find($data['gacha_id']);
        if (empty($gacha) || $gacha->gachaDetails->isEmpty()) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'GachaOrder:0002'), 422);
        }

        // 檢查卡池時間
        if ($gacha->start_timestamp && now()->timestamp < $gacha->start_timestamp) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'GachaOrder:0004'), 422);
        }
        if ($gacha->end_timestamp && now()->timestamp > $gacha->end_timestamp) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'GachaOrder:0004'), 422);
        }

        // 驗證抽取次數
        if (! isset($data['times']) || ! is_numeric($data['times'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'GachaOrder:0003'), 422);
        }

        $times = (int) $data['times'];
        $useFree = isset($data['use_free']) && (int) $data['use_free'] === 1;

        // 免費抽取驗證
        if ($useFree) {
            // 免費抽取只能單抽
            if ($times != 1) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'GachaOrder:0011'), 422);
            }

            // 檢查是否還有免費抽取次數
            $freeDrawCheck = UserGachaOrderService::checkFreeDrawAvailable($user->uid, $gacha);
            if (! $freeDrawCheck['available']) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'GachaOrder:0010'), 422);
            }
        }

        // 根據卡池類型處理
        switch ($gacha->type) {
            case self::TYPE_CHARACTER:
                return $this->handleCharacterGacha($user, $gacha, $times, $useFree);

            case self::TYPE_AVATAR:
            case self::TYPE_EVENT:
                return $this->handleAvatarGacha($user, $gacha, $times, $useFree);

            default:
                return response()->json(ErrorService::errorCode(__METHOD__, 'GachaOrder:0002'), 422);
        }
    }

    public function getLog(Request $request, $gacha_id, $page = 1, $limit = 10)
    {
        $data = $request->input();

        $user = Users::find(auth()->guard('api')->user()->id);
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        if (empty($gacha_id)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'GachaOrder:0001'), 422);
        }

        $offset = ($page - 1) * $limit;

        $userGachaDetails = UserGachaOrderDetails::whereHas('userGachaOrder', function ($query) use ($user, $gacha_id) {
            $query->where('user_id', $user->id)
                ->where('gacha_id', $gacha_id);
        })
            ->with('userGachaOrder')
            ->orderByDesc('id')
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json(['data' => $userGachaDetails], 200);
    }

    /**
     * 處理英雄卡池抽取 (type = 1)
     */
    private function handleCharacterGacha($user, $gacha, $times, $useFree)
    {
        $price = $useFree ? 0 : $gacha->one_price;

        // 使用 Service 處理抽取邏輯
        $result = UserGachaOrderService::createCharacterGacha($user, $gacha, $times, $price, $useFree);
        if (! $result['success']) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
        }

        // 更新統計
        UserStats::where('uid', $user->uid)->increment('draw_hero_times', $times);

        // 處理任務系統
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);

        return response()->json([
            'data' => [
                'gacha_id' => $gacha->id,
                'type' => $gacha->type,
                'times' => $result['total_draws'],
                'is_free' => $result['is_free'],
                'pity' => $result['pity'],
                'get_items' => $result['get_items'],
                'user_free_draw_info' => $result['user_free_draw_info'],
            ],
            'finishedTask' => $formattedTaskResult,
        ], 200);
    }

    /**
     * 處理紙娃娃與家具扭蛋抽取 (type = 3)
     */
    private function handleAvatarGacha($user, $gacha, $times, $useFree)
    {
        // 檢查貨幣道具是否存在
        $currency_item = UserItemService::getItem($gacha->currency_item_id);
        if (empty($currency_item)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0002'), 422);
        }

        $price = $useFree ? 0 : ($times == 1 ? $gacha->one_price : $gacha->ten_price);

        // 使用 Service 處理抽取邏輯
        $result = UserGachaOrderService::create($user, $gacha, $times, $price, $useFree);
        if (! $result['success']) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
        }

        // 處理任務系統
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);

        return response()->json([
            'data' => [
                'gacha_id' => $gacha->id,
                'type' => $gacha->type,
                'times' => $result['total_draws'],
                'is_free' => $result['is_free'],
                'pity' => $result['pity'],
                'get_items' => $result['get_items'],
                'user_free_draw_info' => $result['user_free_draw_info'],
            ],
            'finishedTask' => $formattedTaskResult,
        ], 200);
    }

    /**
     * 玩家任務處理
     */
    private function handleUserTasks($user, $userSurgameInfo)
    {
        $taskService = new TaskService;
        $userStatsService = new UserStatsService($taskService);
        $taskStatsService = new UserStatsService($taskService, $taskService->keywords(), [$taskService, 'calculateStat']);
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $taskService->autoAssignTasks($user->uid);
        $taskStatsService->updateByKeyword($user, 'hero');
        $taskStatsService->updateByKeyword($user, 'skin');
        $taskStatsService->updateByKeyword($user, 'treasure');
        $gradeSerivce = new GradeTaskService;
        $gradeSerivce->autoAsignGradeTask($userSurgameInfo);
        $gradeSerivce->updateByKeyword($user, 'hero');
        $completedTask = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);

        return $formattedTaskResult;
    }
}
