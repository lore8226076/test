<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GddbSurgameDailyResource;
use App\Models\UserItemLogs;
use App\Models\UserItems;
use App\Models\UserSurGameInfo;
use App\Models\UserSurgameResourceStageRecord;
use App\Service\ErrorService;
use App\Service\GradeTaskService;
use App\Service\MonthCardService;
use App\Service\TaskService;
use App\Service\UserItemService;
use App\Service\UserStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSurgameResourceController extends Controller
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

    // 清除關卡
    public function markCleared(Request $request, $type = null)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        if ($type === null) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0005'), 400);
        }

        $data = $request->only('stage_unique_id');
        if (empty($data['stage_unique_id']) || ! is_numeric($data['stage_unique_id'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0001'), 422);
        }

        $stageId = (int) $data['stage_unique_id'];

        // 檢查是否已完成該關卡
        $recordExists = UserSurgameResourceStageRecord::forUser($user->uid)
            ->where('stage_unique_id', $stageId)
            ->whereNotNull('cleared_at')
            ->exists();

        if ($recordExists) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0002'), 422);
        }

        // 驗證關卡資料
        $stageData = $this->validateStage($stageId, $type);
        if ($stageData instanceof JsonResponse) {
            return $stageData;
        }

        // 檢查該類型的前一個關卡是否已完成（第一關除外）
        $previousStage = GddbSurgameDailyResource::where('type', $type)
            ->where('unique_id', '<', $stageId)
            ->orderBy('unique_id', 'desc')
            ->first();

        if ($previousStage) {
            $previousStageCleared = UserSurgameResourceStageRecord::forUser($user->uid)
                ->where('stage_unique_id', $previousStage->unique_id)
                ->whereNotNull('cleared_at')
                ->exists();

            if (! $previousStageCleared) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0012'), 422);
            }
        }

        // 發放首通獎勵
        $rewardResult = $this->distributeRewards(
            $user,
            $stageId,
            $stageData->first_reward,
            '資源關卡首通獎勵',
            $type,
            true
        );

        if ($rewardResult instanceof JsonResponse) {
            return $rewardResult;
        }

        // 標記關卡已完成
        $record = UserSurgameResourceStageRecord::markCleared($user->uid, $stageId, $type);
        $nextStage = GddbSurgameDailyResource::where('type', $type)
            ->where('unique_id', '>', $stageId)
            ->orderBy('unique_id', 'asc')
            ->first();
        $resultData = [
            'stage_unique_id' => $stageId,
            'cleared_at' => $record->cleared_at,
            'rewards' => $stageData->first_reward ?? [],
            'next_stage_unique_id' => $nextStage->unique_id ?? -1,
        ];

        $formattedTaskResult = $this->handleUserTasks($user, null, 'firstClear', $type);

        return response()->json([
            'data' => $resultData,
            'finishedTask' => $formattedTaskResult,
        ], 200);
    }

    // 掃蕩關卡
    public function sweepStage(Request $request, $type = null)
    {

        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        if ($type === null) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0005'), 400);
        }

        $data = $request->only('stage_unique_id', 'use_paid');

        // 驗證關卡ID
        if (empty($data['stage_unique_id']) || ! is_numeric($data['stage_unique_id'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0001'), 422);
        }

        $stageId = (int) $data['stage_unique_id'];
        $usePaid = isset($data['use_paid']) ? (bool) $data['use_paid'] : false;

        // 檢查是否已完成該關卡（必須先通關才能掃蕩）
        $recordExists = UserSurgameResourceStageRecord::forUser($user->uid)
            ->where('stage_unique_id', $stageId)
            ->whereNotNull('cleared_at')
            ->exists();

        if (! $recordExists) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0007'), 422);
        }

        // 驗證關卡資料
        $stageData = $this->validateStage($stageId, $type);
        if ($stageData instanceof JsonResponse) {
            return $stageData;
        }

        // 取得用戶遊戲資訊
        $userGameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        if (! $userGameInfo) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0009'), 422);
        }

        // 檢查掃蕩次數是否足夠
        $availability = $userGameInfo->checkSweepAvailability($type);

        // 檢查是否有無限掃蕩月卡
        $hasUnlimitedSweep = MonthCardService::hasUnlimitedQuickPatrol($user->uid);

        if ($usePaid) {
            // 使用付費掃蕩
            if (!$hasUnlimitedSweep && $availability['pay_left'] < 1) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0008'), 422);
            }

            // 檢查付費貨幣是否足夠
            $cost = $userGameInfo->sweep_pay_amount;
            $userItem = UserItems::where('uid', $user->uid)
                ->where('item_id', $userGameInfo->sweep_pay_item_id)
                ->first();

            if (! $userItem || $userItem->qty < $cost) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0010'), 422);
            }
        } else {
            // 使用免費掃蕩
            if (!$hasUnlimitedSweep && $availability['free_left'] < 1) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0011'), 422);
            }
        }
        // 使用 Transaction 確保操作的原子性
        try {
            \DB::beginTransaction();

            // 計算加成後的獎勵（從月卡系統取得）
            $bonusRate       = MonthCardService::getStageRewardPercent($user->uid);
            $adjustedRewards = [];
            foreach ($stageData->reward as $reward) {
                $baseAmount = $reward['amount'];
                $bonusAmount = floor($baseAmount * $bonusRate / 100);
                $finalAmount = $baseAmount + $bonusAmount;

                $adjustedRewards[] = [
                    'item_id' => $reward['item_id'],
                    'amount' => $finalAmount,
                ];
            }

            // 發放掃蕩獎勵
            $rewardResult = $this->distributeRewards(
                $user,
                $stageId,
                $adjustedRewards,
                '資源關卡掃蕩獎勵',
                $type,
                true
            );

            if ($rewardResult instanceof JsonResponse) {
                \DB::rollBack();

                return $rewardResult;
            }

            if ($usePaid) {
                // 扣除付費貨幣
                $cost = $userGameInfo->sweep_pay_amount;
                $deductResult = UserItemService::removeItem(
                    $this->mapLogTypeToItemLogType($type),
                    $user->id,
                    $user->uid,
                    $userGameInfo->sweep_pay_item_id,
                    $cost,
                    1,
                    "資源關卡付費掃蕩 (Stage ID: {$stageId})"
                );

                if ($deductResult['success'] != 1) {
                    \DB::rollBack();

                    return response()->json(ErrorService::errorCode(__METHOD__, $deductResult['error_code']), 500);
                }

                // 扣除付費掃蕩次數（有無限掃蕩月卡則不扣除）
                if (!$hasUnlimitedSweep && !$userGameInfo->consumePaySweep($type, 1)) {
                    \DB::rollBack();

                    return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0008'), 422);
                }
            } else {
                // 扣除免費掃蕩次數（有無限掃蕩月卡則不扣除）
                if (!$hasUnlimitedSweep && !$userGameInfo->consumeFreeSweep($type, 1)) {
                    \DB::rollBack();

                    return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0011'), 422);
                }
            }

            \DB::commit();
            $formattedTaskResult = $this->handleUserTasks($user, null, 'sweep', $type);

            // 重新載入以獲取最新的掃蕩次數
            $userGameInfo->refresh();
            $type = $userGameInfo->mapStageTypeToSweepType($type);
            $resultData = [
                'stage_unique_id' => $stageId,
                'used_paid' => $usePaid,
                'resource_stage_bonus_rate' => $bonusRate,
                'rewards' => $adjustedRewards,
                'remaining_sweeps' => [
                    'free_left' => $userGameInfo->{"{$type}_sweep_free_left"},
                    'pay_left' => $userGameInfo->{"{$type}_sweep_pay_left"},
                ],
            ];

            return response()->json([
                'data' => $resultData,
                'finishedTask' => $formattedTaskResult,
            ], 200);

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('資源關卡掃蕩失敗', [
                'user_id' => $user->id,
                'stage_id' => $stageId,
                'use_paid' => $usePaid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0004'), 500);
        }
    }

    // 檢查是否已清除關卡
    public function checkCleared(Request $request)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        $stageId = $request->input('stage_unique_id');
        if (empty($stageId) || ! is_numeric($stageId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0001'), 422);
        }

        $exists = UserSurgameResourceStageRecord::forUser($user->uid)
            ->where('stage_unique_id', (int) $stageId)
            ->exists();

        return response()->json(['data' => ['cleared' => $exists]], 200);
    }

    // 取得已清除關卡列表
    public function listCleared(Request $request)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        $perPage = (int) $request->input('per_page', 50);
        if ($perPage <= 0) {
            $perPage = 50;
        }

        $query = UserSurgameResourceStageRecord::forUser($user->uid)
            ->whereNotNull('cleared_at')
            ->orderBy('cleared_at', 'desc');

        if ($request->has('page')) {
            $result = $query->paginate($perPage);
        } else {
            $result = $query->get();
        }

        return response()->json(['data' => $result], 200);
    }

    // 取得玩家關卡進度
    public function getUserStageProgress(Request $request)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }
        // 確保有使用者遊戲資訊
        $userGameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        if (! $userGameInfo) {
            $userGameInfo = UserSurGameInfo::createInitialData($user->uid);
        }

        $types = ['DailyMoney', 'DailyExp', 'MusesGift'];
        $result = [];

        foreach ($types as $type) {
            $sweepKey = $userGameInfo->mapStageTypeToSweepType($type);

            $sweepFreeTotal = $userGameInfo->{"{$sweepKey}_sweep_free_total"} ?? 0;
            $sweepFreeLeft = $userGameInfo->{"{$sweepKey}_sweep_free_left"} ?? 0;
            $sweepPayTotal = $userGameInfo->{"{$sweepKey}_sweep_pay_total"} ?? 0;
            $sweepPayLeft = $userGameInfo->{"{$sweepKey}_sweep_pay_left"} ?? 0;

            $sweepPayItemId = $userGameInfo->sweep_pay_item_id ?? null;
            $sweepPayAmount = $userGameInfo->sweep_pay_amount ?? null;
            // 從月卡系統取得獎勵加成
            $bonusRate      = MonthCardService::getStageRewardPercent($user->uid);

            // 取得該類型已清關卡的最大 unique_id
            $clearedMax = UserSurgameResourceStageRecord::forUser($user->uid)
                ->join('gddb_surgame_daily_resource', 'user_surgame_resource_stage_records.stage_unique_id', '=', 'gddb_surgame_daily_resource.unique_id')
                ->where('gddb_surgame_daily_resource.type', $type)
                ->whereNotNull('user_surgame_resource_stage_records.cleared_at')
                ->max('user_surgame_resource_stage_records.stage_unique_id');

            // 決定 current_stage_id：取已清關卡之後的下一個關卡，如沒有則取最後一個或已清最大
            $nextStage = GddbSurgameDailyResource::where('type', $type)
                ->when($clearedMax, function ($q) use ($clearedMax) {
                    return $q->where('unique_id', '>', $clearedMax);
                })
                ->orderBy('unique_id', 'asc')
                ->first();

            if ($nextStage) {
                $currentStageId = $nextStage->unique_id;
            } else {
                // 如果沒有更後面的關卡，嘗試取最後一個關卡，若也沒有則使用已清最大
                $lastStage = GddbSurgameDailyResource::where('type', $type)->orderBy('unique_id', 'desc')->first();
                $currentStageId = $lastStage?->unique_id ?? ($clearedMax ?? null);
            }

            // 可掃蕩關卡id：已清除的最大那筆，沒有就是 -1
            $sweepableStageId = $clearedMax !== null ? (int) $clearedMax : -1;

            $result[$type] = [
                'sweep_free_total' => (int) $sweepFreeTotal,
                'sweep_free_left' => (int) $sweepFreeLeft,
                'sweep_pay_total' => (int) $sweepPayTotal,
                'sweep_pay_left' => (int) $sweepPayLeft,
                'sweep_pay_item_id' => $sweepPayItemId !== null ? (int) $sweepPayItemId : null,
                'sweep_pay_amount' => $sweepPayAmount !== null ? (int) $sweepPayAmount : null,
                'resource_stage_bonus_rate' => (int) $bonusRate,
                'current_stage_id' => $currentStageId !== null ? (int) $currentStageId : null,
                'sweepable_stage_id' => $sweepableStageId,
            ];
        }

        return response()->json(['data' => $result], 200);
    }

    /**
     * 驗證關卡資料
     *
     * @return GddbSurgameDailyResource|JsonResponse
     */
    private function validateStage(int $stageId, string $type)
    {
        $stageData = GddbSurgameDailyResource::where([
            'unique_id' => $stageId,
            'type' => $type,
        ])->first();

        if (! $stageData) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0003'), 422);
        }

        return $stageData;
    }

    /**
     * 發放獎勵
     *
     * @param  object  $user
     * @return true|JsonResponse
     */
    private function distributeRewards($user, int $stageId, array $rewards, string $memo, $stageType = null, $firstClear = false)
    {
        try {
            if (empty($rewards) || ! is_array($rewards)) {
                return true;
            }

            foreach ($rewards as $reward) {
                if (isset($reward['item_id']) && isset($reward['amount'])) {
                    $itemId = (int) $reward['item_id'];
                    $qty = (int) $reward['amount'];
                    if ($firstClear) {
                        $logType = UserItemLogs::TYPE_FIRST_CLEAR;
                    } else {
                        $logType = $this->mapLogTypeToItemLogType($stageType);
                    }

                    if ($itemId > 0 && $qty > 0) {
                        $result = UserItemService::addItem(
                            $logType,
                            $user->id,
                            $user->uid,
                            $itemId,
                            $qty,
                            1,
                            "{$memo} (Stage ID: {$stageId})"
                        );

                        if ($result['success'] != 1) {
                            \Log::error('資源關卡獎勵發放失敗', [
                                'user_id' => $user->id,
                                'stage_id' => $stageId,
                                'item_id' => $itemId,
                                'qty' => $qty,
                                'memo' => $memo,
                                'error_code' => $result['error_code'],
                            ]);

                            return response()->json(
                                ErrorService::errorCode(__METHOD__, $result['error_code']),
                                500
                            );
                        }
                    }
                }
            }

            return true;

        } catch (\Exception $e) {
            \Log::error('資源關卡獎勵處理失敗', [
                'user_id' => $user->id,
                'stage_id' => $stageId,
                'memo' => $memo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME_RESOURCE:0004'), 500);
        }
    }

    // 玩家任務處理
    private function handleUserTasks($user, $userSurgameInfo, $passType = 'firstClear', $type = null)
    {
        // ============ 任務系統 ============
        $taskService = new TaskService;
        $userStatsService = new UserStatsService($taskService);
        $taskStatsService = new UserStatsService($taskService, $taskService->keywords(), [$taskService, 'calculateStat']);
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $taskService->autoAssignTasks($user->uid);
        $gradeSerivce = new GradeTaskService;
        $gradeSerivce->autoAsignGradeTask($userSurgameInfo);
        // 根據通關類型和關卡類型更新任務進度

        $gradeSerivce->updateByKeyword($user, 'maze');
        $taskStatsService->updateByKeyword($user, 'maze');

        $completedTask = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);

        // ============ 任務系統 ============
        return $formattedTaskResult;
    }

    // userItemLogs 轉換
    private function mapLogTypeToItemLogType($logType)
    {
        $mapping = [
            'DailyMoney' => UserItemLogs::TYPE_SWEEP_MONEY_RESOURCE,
            'DailyExp' => UserItemLogs::TYPE_SWEEP_EXP_RESOURCE,
            'MusesGift' => UserItemLogs::TYPE_SWEEP_GIFT_RESOURCE,
        ];

        return $mapping[$logType] ?? null;
    }
}
