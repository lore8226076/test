<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tasks;
use App\Models\UserItemLogs;
use App\Models\UserTasks;
use App\Models\Users;
use App\Models\UserSurGameFunc;
use App\Models\UserSurGameInfo;
use App\Service\ErrorService;
use App\Service\GradeTaskService;
use App\Service\TaskService;
use App\Service\UserItemService;
use App\Service\UserStatsService;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserGradeController extends Controller
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

    // 取得軍階任務
    public function getUserGradeTask(Request $request, GradeTaskService $gradeTaskService)
    {
        $user = auth()->guard('api')->user();
        $uid = $user->uid;

        // 僅允許測試環境
        $allowedUrls = [
            'https://laravel.test/api',
            'https://clang-party-dev.wow-dragon.com.tw/api',
            'https://clang_party_dev.wow-dragon.com.tw/api',
        ];

        if (in_array(config('services.API_URL'), $allowedUrls)) {
            // 給予軍階任務
            $userSurgameInfo = UserSurGameInfo::where('uid', $uid)->first();
            $gradeSerivce = new GradeTaskService;
            $gradeSerivce->autoAsignGradeTask($userSurgameInfo);

            // ============ 任務系統 ============
            // 玩家軍階任務
            $gradeSerivce->updateByKeyword($user, 'player');
        }

        // ============ 任務系統 ============
        $userSurgameInfo = UserSurGameInfo::where('uid', $uid)->first();
        $formattedTaskResult = $this->handleUserTasks($user, $userSurgameInfo);
        // ============ 任務系統 ============

        $userTasks = $gradeTaskService->getUserGradeTasks($uid, true);

        return response()->json(['data' => $userTasks], 200);
    }

    // 軍階任務進度
    public function updateProgress(Request $request)
    {
        $taskService = new TaskService;
        $gradeTaskService = new GradeTaskService;
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $taskId = $request->input('task_id');
        if (empty($taskId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'TASK:0001'), 422);
        }
        $id = $request->input('process_id');
        // 檢查是否有接任務
        $userTask = $taskService->getUserTask($uid, $taskId, $id);
        if (! $userTask) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'TASK:0001'), 422);
        }

        $progress = $request->input('progress');
        try {
            // 提交進度
            $taskService->submitProgress($uid, $taskId, $progress);
            $result = $gradeTaskService->getUserGradeTasks($uid);

            return response()->json($result, 200);
        } catch (\Exception $e) {

            \Log::error('任務進度更新失敗', [
                'message' => $e->getMessage(),
                'data' => $userTask,
            ]);

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function claimGradeReward(Request $request)
    {
        $taskService = new TaskService;
        $gradeTaskService = new GradeTaskService;

        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $taskId = $request->input('task_id');
        $processId = $request->input('process_id');

        if (empty($taskId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'TASK:0001'), 422);
        }

        try {
            $result = DB::transaction(function () use ($user, $uid, $taskId, $processId, $gradeTaskService) {

                $userTask = UserTasks::where('uid', $uid)
                    ->where('task_id', $taskId)
                    ->where('id', $processId)
                    ->lockForUpdate()
                    ->first();

                if (empty($userTask)) {
                    throw new Exception('TASK:0001');
                }
                if ($userTask->status !== 'completed') {
                    throw new Exception('TASK:0003');
                }
                if ($userTask->reward_status == 1) {
                    throw new Exception('TASK:0005');
                }

                $rewardConfig = $userTask->task->reward ?? Tasks::find($taskId)?->reward;
                $reward = $this->convertRewards($rewardConfig);

                $addItemResult = UserItemService::addItem(
                    UserItemLogs::TYPE_GRADE_TASK,
                    $user->id,
                    $uid,
                    $reward['item_id'],
                    $reward['amount'],
                    1,
                    '軍階任務獎勵領取'
                );

                if ($addItemResult['success'] == 0) {
                    throw new Exception('UserItem:0002');
                }

                $finalReward = [];
                if (isset($addItemResult['character_item_id']) && ! empty($addItemResult['character_item_id'])) {
                    $finalReward[] = [
                        'item_id' => $addItemResult['character_item_id'],
                        'amount' => $addItemResult['character_qty'],
                    ];
                } elseif (isset($addItemResult['item_id'])) {
                    $finalReward[] = [
                        'item_id' => $addItemResult['item_id'],
                        'amount' => $addItemResult['qty'],
                    ];
                } else {
                    $finalReward[] = $reward;
                }

                // 5. 更新任務狀態 (標記為已領取)
                $userTask->reward_status = 1;
                $userTask->save();

                $userSurGameInfo = UserSurGameInfo::where('uid', $uid)->lockForUpdate()->first();

                $canUpgradeGrade = $gradeTaskService->checkAllTaskProcess($userSurGameInfo);
                $upgradeRewards = []; // 用於回傳顯示

                if ($canUpgradeGrade) {
                    $currentGradeReward = $gradeTaskService->getCurrentGradeReward($userSurGameInfo->grade_level);

                    if (is_array($currentGradeReward)) {
                        // 發送升級獎勵
                        if (isset($currentGradeReward['item_reward'])) {
                            $rItem = $currentGradeReward['item_reward'];
                            UserItemService::addItem(
                                UserItemLogs::TYPE_GRADE_UPGRADE,
                                $user->id,
                                $user->uid,
                                $rItem['item_id'],
                                $rItem['amount'],
                                1,
                                '主角軍階獎勵'
                            );
                        }
                        // 開放功能
                        if (isset($currentGradeReward['func_reward'])) {
                            UserSurGameFunc::firstOrCreate(
                                ['uid' => $uid, 'func_key' => $currentGradeReward['func_reward']['func_key']],
                                ['uid' => $uid, 'func_key' => $currentGradeReward['func_reward']['func_key']]
                            );
                        }
                        $upgradeRewards = $currentGradeReward;
                    }

                    // 升級
                    $userSurGameInfo->increment('grade_level', 1);
                    $userSurGameInfo->refresh();

                    // 自動接取新任務
                    $autoSignResult = $gradeTaskService->autoAsignGradeTask($userSurGameInfo);
                    if (empty($autoSignResult)) {
                        throw new Exception('GRADE:0004');
                    }
                }

                return $this->formatterClaimResult($uid, $upgradeRewards, $canUpgradeGrade);
            });

            return response()->json($result, 200);

        } catch (Exception $e) {

            $msg = $e->getMessage();

            // Log 錯誤以便除錯
            Log::error('軍階任務領獎失敗', [
                'uid' => $uid,
                'task_id' => $taskId,
                'error' => $msg,
            ]);

            if (str_contains($msg, ':')) {
                return response()->json(ErrorService::errorCode(__METHOD__, $msg), 422);
            }

            return response()->json(['error' => '系統錯誤', 'debug' => $msg], 400);
        }
    }

    // 軍階獎勵
    public function formatterClaimResult($uid, $gradeUpgradeReward = null, $canUpgradeGrade = false)
    {
        $service = new GradeTaskService;
        $userSurGameInfo = UserSurGameInfo::with('gddbSurgameGrade')->where('uid', $uid)->first();

        $itemReward = [];
        $funcReward = [];
        if (is_array($gradeUpgradeReward)) {
            if (isset($gradeUpgradeReward['item_reward'])) {
                $itemReward = $gradeUpgradeReward['item_reward'];
            }

            if (isset($gradeUpgradeReward['func_reward'])) {
                $funcReward = $gradeUpgradeReward['func_reward'];
            }
        }

        $result = [];
        $result['data'] = $service->getUserGradeTasks($uid, true);
        $result['upgrade_grade'] = [
            'can_upgrade_grade' => $canUpgradeGrade,
            'current_grade_manager_id' => $userSurGameInfo?->gddbSurgameGrade?->unique_id ?? 1,
        ];
        if (! empty($itemReward)) {
            $result['upgrade_grade']['item_reward'] = $itemReward;
        }
        if (! empty($funcReward)) {
            $result['upgrade_grade']['func_reward'] = $funcReward;
        }

        return $result;
    }

    // 轉換資料
    private function convertRewards($input)
    {
        $output = [];
        if (! empty($input)) {
            $output = [
                'item_id' => $input[0],
                'amount' => $input[1],
            ];
        }

        return $output;
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
        $gradeSerivce->updateByKeyword($user, 'chapter');
        $gradeSerivce->updateByKeyword($user, 'hero');
        $completedTask = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);

        // ============ 任務系統 ============
        return $formattedTaskResult;
    }
}
