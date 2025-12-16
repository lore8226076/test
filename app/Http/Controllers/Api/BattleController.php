<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserItemLogs;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\UserItemService;
use Illuminate\Http\Request;

class BattleController extends Controller
{
    const REVIVE_ITEM_ID = 100; // 復活道具, 商城幣

    const REVIVE_ITEM_COST = 100; // 復活道具消耗數量

    public function __construct(Request $request)
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => []]);
        }
    }

    /**
     * 戰鬥中復活
     */
    public function revive(Request $request)
    {
        $user = Users::find(auth()->guard('api')->user()->id);
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        // 檢查玩家是否有足夠的復活道具（item_id = 100，qty = 20）
        $result = UserItemService::removeItem(
            UserItemLogs::TYPE_ITEM_USE,
            $user->id,
            $user->uid,
            self::REVIVE_ITEM_ID,
            self::REVIVE_ITEM_COST,
            1,
            '戰鬥復活使用道具',
            null,
            null,
            null
        );

        if (! $result['success']) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
        }

        return response()->json([
            'success' => 1,
            'message' => '復活成功',
            'data' => [
                'item_used' => [
                    'item_id' => self::REVIVE_ITEM_ID,
                    'qty' => self::REVIVE_ITEM_COST,
                ],
            ],
        ], 200);
    }
}
