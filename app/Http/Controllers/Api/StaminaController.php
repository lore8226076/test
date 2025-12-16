<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\ErrorService;
use App\Service\StaminaService;
use App\Service\TaskService;
use App\Service\GradeTaskService;
use App\Service\UserStatsService;
use App\Service\UserItemService;
use App\Models\UserSurGameInfo;
use App\Models\UserItemLogs;

use Illuminate\Http\Request;

class StaminaController extends Controller
{
    public function __construct(Request $request)
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['']]);
        }
    }

    /** 取得當前體力 */
    public function getCurrentStamina(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid;

        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $stamina = StaminaService::getStamina($uid);
        $stamina_info = StaminaService::getStaminaInfo($uid);

        return response()->json(['data' => ['stamina' => $stamina, 'stamina_info' => $stamina_info]]);
    }

    /** 購買體力 */
    public function purchaseStamina(Request $request)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        $uid = $user->uid;
        $remark = '商城購買體力';
        // 建立static 的資訊 扣除30商城幣
        $result = UserItemService::removeItem(10,
            $user->id,
            $user->uid,
            StaminaService::STAMINA_PURCHASE_CURRENCY_ITEM_ID,
            StaminaService::STAMINA_PURCHASE_PRICE,
            1,
            $remark);
        if (empty($result['success'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);

        }

        // 購買前同步回復
        StaminaService::syncStamina($uid);
        // 體力+最大回體數量
        StaminaService::changeStamina($uid, StaminaService::STAMINA_PURCHASE_RECOVER, $remark, 'purchase');

        // 回傳最新狀態
        $stamina = StaminaService::getStamina($uid);

        // ============ 任務系統 ============
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);
        // ============ 任務系統 ============

        return response()->json(['data' => ['stamina' => $stamina], 'finishedTask' => $formattedTaskResult]);
    }

    /** 掃蕩功能 */
    public function sweep(Request $request)
    {
        $uid = $request->user()->uid;
        if (! $uid) {
            return response()->json(['error' => '請先登入'], 422);
        }

        $cost = (int) $request->input('cost');
        $stageId = $request->input('stage_id');
        $type = $request->input('type', 'manual');
        $remark = '掃蕩功能';

        // 先同步（補齊自然回血）
        StaminaService::syncStamina($uid);

        // 再取最新狀態
        $stamina = StaminaService::getStamina($uid);
        $current = $stamina['current'];

        // 如果補滿後還是不夠扣
        if ($current < $cost) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'STAMINA:0001'), 422);
        }

        // 扣除
        StaminaService::changeStamina($uid, -$cost, $remark, $type, $stageId);

        // 回傳最新狀態
        $stamina = StaminaService::getStamina($uid);

        return response()->json(['data' => ['stamina' => $stamina]]);
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
        $taskStatsService->updateByKeyword($user, 'buy_stamina');
        $taskStatsService->updateByKeyword($user, 'diamond');
        $completedTask = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);

        // ============ 任務系統 ============
        return $formattedTaskResult;
    }
}
