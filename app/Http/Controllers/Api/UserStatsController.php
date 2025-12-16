<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserMaps;
use App\Models\Users;
use App\Service\MapManageService;
use App\Service\TaskService;
use App\Service\UserStatsService;
use Illuminate\Http\Request;

class UserStatsController extends Controller
{
    protected $userStatsService;
    protected $taskService;
    protected $mapManageService;

    public function __construct(Request $request, UserStatsService $userStatsService, TaskService $taskService, MapManageService $mapManageService)
    {
        $this->userStatsService = $userStatsService;
        $this->taskService      = $taskService;
        $this->mapManageService = $mapManageService;
        $origin                 = $request->header('Origin');
        $referer                = $request->header('Referer');
        $referrerDomain         = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['updateUserStats']]);
        }
    }

    /** 更新玩家統計資料 */
    public function updateUserStats(Request $request)
    {
        $rawStatsData = $request->data;

        if (! is_array($rawStatsData)) {
            $rawStatsData = json_decode($rawStatsData, true);
        }

        $results     = [];
        $taskService = new TaskService();

        foreach ($rawStatsData as $userUid => $userStats) {
            if ($userUid === 'map_id') {
                continue;
            }


            if (! is_array($userStats)) {
                $decoded = json_decode($userStats, true);
                if (is_array($decoded)) {
                    $userStats = $decoded;
                } else {
                    continue;
                }
            }

            $user = Users::where('uid', $userUid)->first();
            if (empty($user)) {
                continue;
            }

            foreach ($userStats as $statKey => $statValue) {
                if (array_key_exists($statKey, $this->userStatsService->keywords())) {
                    $this->userStatsService->updateByKeyword($user, $statKey, [], $statValue);
                } else {
                    $keyword = $this->userStatsService->getKeywordByColumn($statKey);
                    if ($keyword) {
                        $this->userStatsService->updateByKeyword($user, $keyword, [$statKey], $statValue);
                    } else {
                        \Log::warning("無法處理 statKey '$statKey'");
                    }
                }
                $this->updateUserStatsRelatedTask($userUid, $statKey, $statValue);
            }

            $completedTasks       = $taskService->getCompletedTasks($user->uid);
            $formattedTaskResults = $taskService->formatCompletedTasks($completedTasks);

            $results[] = [
                'uid'          => $userUid,
                'finishedTask' => $formattedTaskResults,
            ];
        }

        if (isset($rawStatsData['map_id'])) {
            $clearCount = count($rawStatsData) - 1; // 扣掉 map_id
            $map        = UserMaps::where('id', (int) $rawStatsData['map_id'])->first();
            $this->mapManageService->increaseClearCount($map, $clearCount);
            $this->mapManageService->increasePlayCount($map, $clearCount);
        }

        return response()->json(['message' => '更新成功', 'data' => $results]);
    }

    /** 更新關聯任務 */
    public function updateUserStatsRelatedTask($uid, $recordName, $value = null)
    {
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        // taskStatsService
        $taskService      = new TaskService();
        $keywords         = $taskService->keywords();
        $taskStatsService = new UserStatsService($taskService, $keywords, [$taskService, 'calculateStat']);
        $taskStatsService->updateByKeyword($user, $recordName, [], $value);

        $completedTasks = $this->userStatsService->updateByKeyword($user, $recordName);
        return response()->json(['message' => '更新成功', 'data' => $completedTasks]);
    }

}
