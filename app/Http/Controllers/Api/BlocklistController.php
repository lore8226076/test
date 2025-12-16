<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Service\BlocklistService;
use App\Service\ErrorService;
use Illuminate\Http\Request;

class BlocklistController extends Controller
{
    protected $blocklistService;

    public function __construct(Request $request, BlocklistService $blocklistService)
    {
        $this->blocklistService = $blocklistService;

        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['']]);
        }
    }

    // 取得我封鎖的人
    public function getBlockedUsers(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $result = $this->blocklistService->getBlockedUsers($uid);
        if (isset($result['success']) && $result['success'] == 0) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
        }

        return response()->json(['data' => $result], 200);
    }

    // 檢查對方是否封鎖我
    public function checkBlocked(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $toUid = $request->input('check_uid');
        if (empty($toUid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $result = BlocklistService::isBlocked($toUid, $uid);

        return response()->json(['data' => $result], 200);
    }

    // 封鎖使用者
    public function block(Request $request)
    {
        $fromUid = auth()->guard('api')->user()->uid;
        if (empty($fromUid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $toUid = $request->blocked_uid;
        if ($response = $this->checkUserExists($toUid)) {
            return $response;
        }

        // 使用者不存在
        $result = $this->blocklistService->block($fromUid, $toUid);
        if (isset($result['success']) && $result['success'] == 0) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
        }

        return response()->json(['data' => $result], 200);
    }

    // 解除封鎖使用者
    public function unblock(Request $request)
    {
        $fromUid = auth()->guard('api')->user()->uid;
        if (empty($fromUid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $toUid = $request->blocked_uid;
        if ($response = $this->checkUserExists($toUid)) {
            return $response;
        }
        // 使用者不存在
        $result = $this->blocklistService->unblock($fromUid, $toUid);
        if (isset($result['success']) && $result['success'] == 0) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
        }

        return response()->json(['data' => $result], 200);
    }

    // 檢查使用者是否存在
    private function checkUserExists($uid)
    {
        if (empty($uid) || ! Users::where('uid', $uid)->exists()) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
    }
}
