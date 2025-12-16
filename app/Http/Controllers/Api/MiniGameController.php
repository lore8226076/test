<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MiniGameRanks;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\TaskService;
use App\Service\UserStatsService;
use Illuminate\Http\Request;

class MiniGameController extends Controller
{
    public function __construct(Request $request)
    {
        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['getRanking']]);
        }
    }

    // 創建遊戲紀錄
    public function createRecord(Request $request)
    {
        $uid        = auth()->guard('api')->user()->uid;
        $game_id    = $request->game_id;
        $score      = $request->score;
        $total_time = $request->total_time;

        // 檢查game_id是否存在
        if (! $this->checkGameId($game_id)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 401);
        }

        // 檢查user_id是否存在
        if (! $this->checkUserId($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        // 分數異常
        if ($score < 0 || ! is_numeric($score)) {
            \Log::error('分數異常:' . $score . ', game_id:' . $game_id . ', uid:' . $uid);
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0003'), 401);
        }

        // 總時間異常
        if ($total_time < 0 || ! is_numeric($total_time)) {
            \Log::error('總時間異常:' . $total_time . ', game_id:' . $game_id . ', uid:' . $uid);
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0003'), 401);
        }

        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            \Log::error('user_id異常:' . $uid);
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0003'), 401);
        }

        // 取得目前最高分紀錄
        $record = MiniGameRanks::where('game_id', $game_id)
            ->where('user_id', $user->id)
            ->orderBy('score', 'desc')
            ->orderBy('total_time', 'asc')
            ->first();

        // 如果新分數比目前最高分還高，才更新
        if (empty($record) || $score > $record->score || ($score == $record->score && $total_time < $record->total_time)) {
            if (empty($record)) {
                try {
                    $record = MiniGameRanks::create([
                        'game_id'    => $game_id,
                        'user_id'    => $user->id,
                        'score'      => $score,
                        'total_time' => $total_time,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('創建小遊戲紀錄失敗: ' . $e->getMessage());
                }
            } else {
                $record->score      = $score;
                $record->total_time = $total_time;
                $record->save();
            }
        }

        //============ 任務系統 ============
        // 任務Service
        $taskService = new TaskService();
        // 紀錄系統任務
        $userStatsService = new UserStatsService($taskService);
        // $statsResult      = $userStatsService->updateByKeyword($user, 'mini_game');
        // 玩家任務
        $taskStatsService = new UserStatsService($taskService, $taskService->keywords(), [$taskService, 'calculateStat']);
        // $taskResult       = $taskStatsService->updateByKeyword($user, 'mini_game');
        // 本次登入是否有完成任務
        $completedTask       = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);
        //============ 任務系統 ============

        return response()->json([
            'message'      => 'success',
            'data'         => ['record' => $record],
            'finishedTask' => $formattedTaskResult,
        ], 200);
    }

    // 取得遊戲排行
    public function getRanking($game_id)
    {
        if (! $this->checkGameId($game_id)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 401);
        }
        $ranking = MiniGameRanks::where('game_id', $game_id)
            ->select('user_id', 'score', 'total_time')
            ->with('user', function ($q) {
                return $q->select('id', 'name');
            })
            ->whereHas('user', function ($q) {
                return $q->whereNotNull('name');
            })
            ->orderBy('score', 'desc')
            ->get();

        $ranking->map(function ($rank) {
            $rank->score = round($rank->score, 1);
            return $rank;
        });

        return response()->json([
            'data' => $ranking,
        ], 200);
    }

    // 檢查遊戲是否存在
    private function checkGameId($game_id)
    {
        if (! array_key_exists($game_id, MiniGameRanks::getMiniGameTypes())) {
            return false;
        }
        return true;
    }

    // 檢查user_id是否存在
    private function checkUserId($uid)
    {
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return false;
        }
        return true;
    }

}
