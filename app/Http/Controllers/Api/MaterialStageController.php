<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserItemLogs;
use App\Models\Users;
use App\Models\UserStatus;
use App\Service\ErrorService;
use App\Service\MaterialStageService;
use App\Service\StaminaService;
use App\Service\TaskService;
use App\Service\UserItemService;
use App\Service\UserStatsService;
use Illuminate\Http\Request;

class MaterialStageController extends Controller
{
    protected $materialStageService;

    public function __construct(Request $request, MaterialStageService $materialStageService)
    {
        $this->materialStageService = $materialStageService;
        $origin                     = $request->header('Origin');
        $referer                    = $request->header('Referer');
        $referrerDomain             = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['']]);
        }
    }

    /** 取得關卡列表 */
    public function lists()
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        $uid                     = $user->uid;
        $materialStageCategories = $this->materialStageService->getLists($uid);
        return response()->json(['data' => $materialStageCategories]);
    }

    /** 進入關卡（支援掃蕩） */
    public function enterMaterialStage(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $stageId    = $request->input('stage_id');
        $sweepCount = (int) $request->input('sweep_count', 0);

        // 限制掃蕩次數
        if ($sweepCount < 0) {
            $sweepCount = 0;
        }

        if ($sweepCount > 10) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SWEEP:0001'), 422);
        }

        $stageInfo = (object) $this->materialStageService->getInfo($stageId);
        if (empty($stageInfo)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'STAGE:0001'), 422);
        }
        $singleCost = $stageInfo->stamina_cost;

        // 先同步
        StaminaService::syncStamina($uid);

        // 再取最新狀態
        $stamina = StaminaService::getStamina($uid);
        $current = $stamina['current'];

        // 一般進入關卡（sweep_count == 0）
        if ($sweepCount === 0) {
            if ($current < $singleCost) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'STAMINA:0001'), 422);
            }

            // 扣體力
            $result = StaminaService::changeStamina($uid, -$singleCost, '進入關卡', 'manual', $stageId);
            if (! $result) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'STAMINA:0002'), 422);
            }

            // 單次獎勵
            $reward        = $this->materialStageService->getRandomReward($stageId);
            $mergedRewards = $this->mergeRewards($reward);
            $grantResult   = $this->grantRewards($mergedRewards, $user, $uid, '材料關卡獎勵領取');
            if ($grantResult !== true) {
                return response()->json(ErrorService::errorCode(__METHOD__, $grantResult), 422);
            }

            $rewards = $mergedRewards;
        } else {
            $totalCost = $singleCost * $sweepCount;
            if ($current < $totalCost) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'STAMINA:0001'), 422);
            }

            $userStatus = UserStatus::where('uid', $uid)->first();
            if ($userStatus->sweep_max < $sweepCount) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'STAGE:0003'), 422);
            }
            if ($userStatus->sweep_count < $sweepCount) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'STAGE:0004'), 422);
            }
            if (! $this->materialStageService->checkStageClearStatus($stageId, $uid)) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'STAGE:0005'), 422);
            }

            // 扣體力
            $result = StaminaService::changeStamina($uid, -$totalCost, '掃蕩關卡', 'manual', $stageId);
            if ($result === false) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'STAMINA:0002'), 422);
            }

            // 扣掃蕩次數
            $userStatus->sweep_count -= $sweepCount;
            $userStatus->save();

            // 多次獎勵合併
            $allRewards = [];
            for ($i = 0; $i < $sweepCount; $i++) {
                $reward     = $this->materialStageService->getRandomReward($stageId);
                $allRewards = array_merge($allRewards, $reward);
            }
            $mergedRewards = $this->mergeRewards($allRewards);
            $grantResult   = $this->grantRewards($mergedRewards, $user, $uid, '材料關卡掃蕩');
            if ($grantResult !== true) {
                return response()->json(ErrorService::errorCode(__METHOD__, $grantResult), 422);
            }

            $rewards = $mergedRewards;
        }

        // 重新取得狀態
        $stamina = StaminaService::getStamina($uid);

        //============ 任務系統 ============
        // 任務Service
        $taskService = new TaskService();
        // 紀錄系統任務
        $userStatsService = new UserStatsService($taskService);
        // $userStatsService->updateByKeyword($user, 'stamina');
        // 玩家任務
        $taskStatsService = new UserStatsService($taskService, $taskService->keywords(), [$taskService, 'calculateStat']);
        // $taskStatsService->updateByKeyword($user, 'stamina');

        // 本次登入是否有完成任務
        $completedTask       = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);
        //============ 任務系統 ============

        //============ 更新通關狀態 ========
        $this->materialStageService->updateStageStatus($stageId, $user);
        //============ 更新通關狀態 ========

        return response()->json(['data' => [
            'stamina'      => $stamina,
            'reward'       => $rewards,
            'finishedTask' => $formattedTaskResult,
        ]]);
    }

    public function resetStatus()
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        $userStatuses              = UserStatus::where('uid', $uid)->first();
        $userStatuses->sweep_count = $userStatuses->sweep_max;
        $userStatuses->save();

        return response()->json(['data' => 'success']);
    }

    public function checkPermission(Request $request)
    {
        $stageId = $request->input('stage_id');
        if (empty($stageId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'STAGE:0001'), 422);
        }

        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $result = $this->materialStageService->checkPermission($stageId, $user);
        return response()->json(['data' => ['permission' => $result]], 200);
    }

    /** 更新玩家關卡狀態 */
    public function updateStageStatus(Request $request)
    {
        $stageId = $request->input('stage_id');
        if (empty($stageId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'STAGE:0001'), 422);
        }

        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $result = $this->materialStageService->updateStageStatus($stageId, $user);
        return response()->json(['data' => ['message' => $result ? '資料更新成功' : '資料更新失敗']], 200);
    }

    private function mergeRewards($rewards)
    {
        $merged = [];
        foreach ($rewards as $item) {
            $itemId = $item['item_id'];
            $amount = $item['amount'];
            if (isset($merged[$itemId])) {
                $merged[$itemId]['amount'] += $amount;
            } else {
                $merged[$itemId] = [
                    'item_id' => $itemId,
                    'amount'  => $amount,
                ];
            }
        }
        return array_values($merged);
    }

    private function grantRewards($mergedRewards, $user, $uid, $logType)
    {
        foreach ($mergedRewards as $item) {
            $itemResult = UserItemService::addItem(UserItemLogs::TYPE_SYSTEM, $user->id, $uid, $item['item_id'], $item['amount'], 1, $logType);
            if ($itemResult['success'] == 0) {
                return $itemResult['error_code'];
            }
        }
        return true;
    }
}
