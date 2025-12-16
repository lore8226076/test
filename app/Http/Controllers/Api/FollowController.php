<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\FollowService;
use App\Service\BlocklistService;
use App\Service\TaskService;
use App\Service\UserStatsService;
use App\Models\Follows;
use App\Models\Blocklist;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    protected $followService;
    protected $blocklistService;

    public function __construct(Request $request, FollowService $followService, BlocklistService $blocklistService)
    {
        $this->followService = $followService;
        $this->blocklistService = $blocklistService;

        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['']]);
        }
    }

    // 取得粉絲列表
    public function getUserFollowers(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $result = $this->followService->getUserFollowers($uid);

        return response()->json(['data' => $result], 200);
    }

    // 取得追蹤名單
    public function getUserFollowings(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid; // 玩家本人
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $result = $this->followService->getUserFollowings($uid);

        return response()->json(['data' => $result], 200);
    }

    // 追蹤
    public function follow(Request $request)
    {
        $followUid = $request->following_uid;
        if ($response = $this->checkUserExists($followUid)) {
            return $response;
        }

        // 使用者不存在
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        // 不能追蹤自己
        if ($uid === $followUid) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'FOLLOW:0001'), 422);
        }

        // 檢查是否為封鎖名單
        $blocklist = $this->blocklistService->getBlockedUsers($uid);
        if (in_array((string) $followUid, array_column($blocklist, 'uid'), true)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'FOLLOW:0006'), 422);
        }

        // 檢查是否為封鎖我的人
        $blockers = $this->blocklistService->getBlockers($followUid);
        if (in_array($followUid, array_column($blockers, 'uid'))) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'BLOCK:0007'), 422);
        }

        $result = $this->followService->follow($uid, $followUid);

        //============ 任務系統 ============
        // 任務Service
        $taskService = new TaskService();
        // 紀錄系統任務
        $userStatsService = new UserStatsService($taskService);
        // $statsResult = $userStatsService->updateByKeyword($user, 'follow');

        // 本次登入是否有完成任務
        $completedTask = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);
        //============ 任務系統 ============

        return response()->json(['data' => $result, 'finishedTask' => $formattedTaskResult], 200);
    }

    // 取消追蹤
    public function unfollow(Request $request)
    {
        $followUid = $request->following_uid;
        if ($response = $this->checkUserExists($followUid)) {
            return $response;
        }

        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        // 不能追蹤自己
        if ($uid === $followUid) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'FOLLOW:0001'), 422);
        }

        $result = $this->followService->unfollow($uid, $followUid);

        return response()->json(['data' => $result], 200);
    }

    // 檢查是否已追蹤
    public function isFollowing($uid)
    {
        $followUid = $uid;
        if ($response = $this->checkUserExists($followUid)) {
            return $response;
        }

        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $result = $this->followService->isFollowing($uid, $followUid);

        return response()->json(['data' => $result], 200);
    }

    // 檢查被追蹤者是否存在
    private function checkUserExists($followUid)
    {
        if (empty($followUid) || ! Users::where('uid', $followUid)->exists()) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
    }

    // 更新備註
    public function updateNote(Request $request, string $follower_uid)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $note = $validated['note'] ?? null;

        $result = $this->followService->updateNote($uid, $follower_uid, $note);
        if ($result['success'] == 0) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
        }

        return response()->json(['data' => $result], 200);
    }

    // 搜尋好友
    public function search(Request $request, $keywords)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        if (empty($keywords)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'FOLLOW:0002'), 422);
        }

        // 關鍵字字數 2~16 字數
        if (mb_strlen($keywords) < 2 || mb_strlen($keywords) > 17) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'FOLLOW:0007'), 422);
        }

        $results = $this->followService->search($uid, $keywords);

        return response()->json(['data' => $results], 200);
    }
}
