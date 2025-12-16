<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemPrices;
use App\Models\UserItemLogs;
use App\Models\Users;
use App\Models\UserSurGameInfo;
use App\Service\ErrorService;
use App\Service\GradeTaskService;
use App\Service\StaminaService;
use App\Service\TaskService;
use App\Service\UserItemService;
use App\Service\UserMallOrderService;
use App\Service\UserStatsService;
use Illuminate\Http\Request;

class UserMallOrderController extends Controller
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

    public function create(Request $request)
    {
        $data = $request->input();

        $user = Users::find(auth()->guard('api')->user()->id);
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        if (empty($data['item_id'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0004'), 422);
        }

        $item = UserItemService::getItem($data['item_id']);
        if (empty($item['region']) || empty($item['category']) || empty($item['type'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0009'), 422);
        }

        if (empty($data['currency_item_id'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0002'), 422);
        }

        $currency_item = UserItemService::getItem($data['currency_item_id']);

        if (empty($currency_item)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0002'), 422);
        }

        if (empty($data['price'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0001'), 422);
        }

        if (empty($data['qty'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0008'), 422);
        }

        $itemPrice = ItemPrices::with('itemGroup')->where('item_id', $item['item_id'])->where('currency_item_id', $currency_item['item_id'])->first();
        if (empty($itemPrice) || $itemPrice->price != $data['price']) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0011'), 422);
        }

        $result = UserMallOrderService::create($user, $item, $currency_item, $itemPrice, $data['qty']);
        if (empty($result['success'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
        }
        // 如果商品是群組道具，則需要將群組道具的道具加入給
        if (! $itemPrice->itemGroup->isEmpty()) {
            foreach ($itemPrice->itemGroup as $itemGroup) {
                $item = UserItemService::getItem($itemGroup->item_id);
                $result = UserItemService::addItem(UserItemLogs::TYPE_SHOP_GROUP_BUY, $user->id, $user->uid, $itemGroup->item_id, $itemGroup->qty, 1, '商城群組道具轉換');
                if (empty($result['success'])) {
                    return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
                }
            }
        }

        // 體力道具禮包 1004要轉換成體力
        if ($itemPrice->item_id == 1004) {
            $result = StaminaService::convertStamina($user->uid, $itemGroup->qty);
        }

        // ============ 任務系統 ============
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);
        // ============ 任務系統 ============

        return response()->json([
            'data' => $result,
            'finishedTask' => $formattedTaskResult,
        ], 200);
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
        $taskStatsService->updateByKeyword($user, 'diamond');
        $completedTask = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);

        // ============ 任務系統 ============
        return $formattedTaskResult;
    }
}
