<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GddbSurgamePassiveReward as Rewards;
use App\Models\UserPatrolReward;
use App\Models\Users;
use App\Models\UserStatus;
use App\Models\UserSurGameInfo;
use App\Service\CharacterService;
use App\Service\ErrorService;
use App\Service\GradeTaskService;
use App\Service\MonthCardService;
use App\Service\StaminaService;
use App\Service\TaskService;
use App\Service\UserItemService;
use App\Service\UserJourneyService;
use App\Service\UserStatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatrolController extends Controller
{
    private int $rewardInterval;

    protected $userJourneyService;

    public function __construct(Request $request, UserJourneyService $userJourneyService)
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $this->userJourneyService = $userJourneyService;
        $this->rewardInterval = 1;
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => []]);
        }
    }

    // 玩家領取巡邏獎勵
    public function claim(Request $request)
    {
        $uid = auth()->guard('api')?->user()?->uid;
        $now = Carbon::now();

        // 檢查玩家是否存在
        $user = Users::where('uid', $uid)->first();
        if (! $user || empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        // 檢查surgame資料是否存在
        $surgameInfo = UserSurGameInfo::where('uid', $uid)->first();
        if (! $surgameInfo) {
            $surgameInfo = UserSurGameInfo::createInitialData($uid);
        }

        // 取得玩家當前進度
        $currentChapterId = 1;
        $currentChapterData = $this->userJourneyService->getCurrentProgress($user->uid);
        if ($currentChapterData && is_array($currentChapterData)) {
            $currentChapterId = $currentChapterData['chapter_id'];
        }

        // 取得月卡巡邏獎勵加成百分比
        $patrolBonusPercent = MonthCardService::getPatrolRewardPercent($uid);

        return DB::transaction(function () use ($uid, $now, $user, $currentChapterId, $patrolBonusPercent) {
            // 保留更新前領獎時間
            $lastClaimedAt = UserPatrolReward::where('uid', $uid)->value('last_claimed_at') ?? null;

            $userReward = UserPatrolReward::lockForUpdate()->firstOrCreate(
                ['uid' => $uid],
                ['last_claimed_at' => $now, 'pending_minutes' => 0]
            );

            $isFirstClaim = $userReward->wasRecentlyCreated;

            $lastClaimTime = Carbon::parse($userReward->last_claimed_at);
            $diffMinutes = $lastClaimTime->diffInMinutes($now);

            $totalMinutes = $diffMinutes + $userReward->pending_minutes;
            $totalMinutes = min($totalMinutes, 24 * 60); // 最多 24 小時

            // 計算可領分鐘數
            $effectiveMinutes = floor($totalMinutes / $this->rewardInterval) * $this->rewardInterval;
            $pendingMinutes = $totalMinutes % $this->rewardInterval;

            // 第一次領至少一段
            if ($isFirstClaim) {
                $effectiveMinutes = max($effectiveMinutes, $this->rewardInterval);
                $pendingMinutes = 0;
            }

            // 不足一段不可領
            if ($effectiveMinutes < $this->rewardInterval) {
                $userReward->pending_minutes = $pendingMinutes;
                $userReward->save();

                $error = ErrorService::errorCode(__METHOD__, 'PATROL:0001');
                $error['data']['last_claimed_at'] = $userReward->last_claimed_at;

                return response()->json($error, 422);
            }

            // 計算獎勵（含月卡加成）
            [$finalRewards, $pendingMinutes, $baseRewards] = $this->calculateRewards(
                $currentChapterId,
                $effectiveMinutes,
                $this->rewardInterval,
                $patrolBonusPercent
            );

            foreach ($finalRewards as $itemId => $amount) {
                UserItemService::addItem('30', $user->id, $user->uid, $itemId, $amount, 1, '巡邏任務獎勵');
            }

            $userReward->last_claimed_at = $now;
            $userReward->pending_minutes = $pendingMinutes;
            $userReward->save();

            $syncResult = CharacterService::syncMainCharacter($user);
            // if ($syncResult['success'] == false) {
            //     return response()->json([
            //         ErrorService::errorCode(__METHOD__, $syncResult['error_code']),
            //     ], 500);
            // }

            return response()->json([
                'data' => [
                    'message' => 'success',
                    'rewards' => collect($finalRewards)->map(fn ($amount, $itemId) => [
                        'item_id' => $itemId,
                        'amount' => $amount,
                        'base_amount' => $baseRewards[$itemId] ?? $amount,
                    ])->values(),
                    'bonus_percent' => $patrolBonusPercent,
                    'last_claimed_at' => $lastClaimedAt,
                    'level_up_state' => [
                        'has_level_up' => $syncResult['success'] ? 1 : 0,
                        'level_reward' => $syncResult['reward'] ?? [],
                    ],
                ],
            ], 200);
        });
    }

    public function quickPatorl(Request $request)
    {
        $uid = auth()->guard('api')?->user()?->uid;

        // 檢查玩家是否存在
        $user = Users::where('uid', $uid)->first();
        if (! $user || empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        // 檢查surgame資料是否存在
        $surgameInfo = UserSurGameInfo::where('uid', $uid)->first();
        if (! $surgameInfo) {
            $surgameInfo = UserSurGameInfo::createInitialData($uid);
        }

        // 檢查是否有無限掃蕩月卡
        $hasUnlimitedSweep = MonthCardService::hasUnlimitedQuickPatrol($uid);

        // 取得目前次數狀態
        $userStatus = UserStatus::where('uid', $uid)->first();
        $currentPatrolCount = $userStatus ? $userStatus->patrol_count : 0;

        // (沒有次數 且 沒有月卡) 才擋下，否則放行
        if ($currentPatrolCount <= 0 && ! $hasUnlimitedSweep) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'PATROL:0004'), 422);
        }

        // 如果現在次數 <= 0 ，代表不強制傳送掃蕩次數到任務
        $forceFinishedPatrolTask = ($currentPatrolCount <= 0);
        // -------------------------------------

        // 扣除體力
        $staminaResult = StaminaService::deductStamina($uid, StaminaService::QUICK_PATROL_STAMINA_COST, '快速巡邏');
        if (empty($staminaResult['success'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, $staminaResult['error_code']), 422);
        }

        // 取得玩家當前進度
        $currentChapterId = 1;
        $currentChapterData = $this->userJourneyService->getCurrentProgress($uid);
        if ($currentChapterData && is_array($currentChapterData)) {
            $currentChapterId = $currentChapterData['chapter_id'];
        }

        // 取得月卡巡邏獎勵加成百分比
        $patrolBonusPercent = MonthCardService::getPatrolRewardPercent($uid);

        return DB::transaction(function () use ($user, $currentChapterId, $patrolBonusPercent, $currentPatrolCount, $forceFinishedPatrolTask) {
            $effectiveMinutes = 24 * 60;

            // 計算獎勵（含月卡加成）
            [$finalRewards, $pendingMinutes, $baseRewards] = $this->calculateRewards(
                $currentChapterId,
                $effectiveMinutes,
                10,
                $patrolBonusPercent
            );

            // 更新玩家物品
            foreach ($finalRewards as $itemId => $amount) {
                UserItemService::addItem(31, $user->id, $user->uid, $itemId, $amount, 1, '快速巡邏任務獎勵');
            }

            // 玩家等級更新
            $syncResult = CharacterService::syncMainCharacter($user);

            if ($currentPatrolCount > 0) {
                $decreaseUserStatus = UserStatus::decreasePatrolCount($user->uid);
                if (! $decreaseUserStatus) {
                    return response()->json(ErrorService::errorCode(__METHOD__, 'PATROL:0003'), 500);
                }
            }
            $currentUserStatus = UserStatus::where('uid', $user->uid)->first();

            // ============ 任務系統 ============
            $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
            $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo, $forceFinishedPatrolTask);
            // ============ 任務系統 ============

            return response()->json([
                'data' => [
                    'message' => 'success',
                    'rewards' => collect($finalRewards)->map(fn ($amount, $itemId) => [
                        'item_id' => $itemId,
                        'amount' => $amount,
                        'base_amount' => $baseRewards[$itemId] ?? $amount,
                    ])->values(),
                    'bonus_percent' => $patrolBonusPercent,
                    'level_up_state' => [
                        'has_level_up' => $syncResult['success'] ? 1 : 0,
                        'level_reward' => $syncResult['reward'] ?? [],
                    ],
                    'patrol_count' => $currentUserStatus->patrol_count,
                    'finishedTask' => $formattedTaskResult,
                ],
            ], 200);
        });
    }

    /**
     * 計算固定與隨機獎勵（含月卡加成）
     *
     * @param  int  $nowStage  當前章節
     * @param  int  $effectiveMinutes  有效分鐘數
     * @param  int  $interval  獎勵間隔（分鐘）
     * @param  int  $bonusPercent  月卡加成百分比
     * @return array [最終獎勵, 剩餘分鐘數, 基礎獎勵]
     */
    private function calculateRewards(int $nowStage, int $effectiveMinutes, int $interval = 10, int $bonusPercent = 0): array
    {
        $stageRewards = Rewards::where('now_stage', $nowStage)->first();
        if (! $stageRewards) {
            return [[], 0, []];
        }

        $hourCoin = $stageRewards->hour_coin;
        $hourExp = $stageRewards->hour_exp;
        $hourCrystal = $stageRewards->hour_crystal;
        $hourPaint = $stageRewards->hour_paint;
        $hourXp = $stageRewards->hour_xp;

        // 計算幾段完整獎勵
        $totalSegments = intdiv($effectiveMinutes, $interval);
        $pendingMinutes = $effectiveMinutes % $interval;

        $baseRewards = [];
        $finalRewards = [];

        // 固定獎勵按比例
        $rewardMap = [
            101 => $hourCoin,
            199 => $hourExp,
            198 => $hourCrystal,
            191 => $hourPaint,
            190 => $hourXp,
        ];

        foreach ($rewardMap as $itemId => $perHour) {
            $perInterval = floor(($perHour / 60) * $interval);
            $baseAmount = $perInterval * $totalSegments;
            if ($baseAmount > 0) {
                $baseRewards[$itemId] = ($baseRewards[$itemId] ?? 0) + $baseAmount;
                // 套用月卡加成
                $bonusAmount = floor($baseAmount * $bonusPercent / 100);
                $finalRewards[$itemId] = ($finalRewards[$itemId] ?? 0) + $baseAmount + $bonusAmount;
            }
        }

        // 隨機獎勵每段抽一次
        $bonusPool = $stageRewards->rand_reward;
        if (empty($bonusPool)) {
            return [$finalRewards, $pendingMinutes, $baseRewards];
        } else {
            if (is_string($bonusPool)) {
                $bonusPool = json_decode($bonusPool, true) ?? [];
            }

            for ($i = 0; $i < $totalSegments; $i++) {
                foreach ($bonusPool as [$itemId, $amount, $chance]) {
                    if (mt_rand(1, 100) <= $chance) {
                        $baseRewards[$itemId] = ($baseRewards[$itemId] ?? 0) + $amount;
                        // 隨機獎勵也套用月卡加成
                        $bonusAmount = floor($amount * $bonusPercent / 100);
                        $finalRewards[$itemId] = ($finalRewards[$itemId] ?? 0) + $amount + $bonusAmount;
                    }
                }
            }
        }

        return [$finalRewards, $pendingMinutes, $baseRewards];
    }

    /**
     * 如果持有月卡
     * 會強制更新任務進度 (目前程式不會檢查掃蕩次數)
     */
    private function handleUserTasks($user, $userSurgameInfo, $forceFinishedPatrolTask = false)
    {
        // ============ 任務系統 ============
        $taskService = new TaskService;
        $userStatsService = new UserStatsService($taskService);
        $taskStatsService = new UserStatsService($taskService, $taskService->keywords(), [$taskService, 'calculateStat']);
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $taskService->autoAssignTasks($user->uid);
        $gradeSerivce = new GradeTaskService;
        $gradeSerivce->autoAsignGradeTask($userSurgameInfo);
        if ($forceFinishedPatrolTask) {
            $gradeSerivce->updateByKeyword($user, 'patrol', ['quick_patrol_count'], $value);
        } else {
            $gradeSerivce->updateByKeyword($user, 'patrol');
        }
        $taskStatsService->updateByKeyword($user, 'patrol'); // 巡邏相關任務更新
        $completedTask = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);

        // ============ 任務系統 ============
        return $formattedTaskResult;
    }
}
