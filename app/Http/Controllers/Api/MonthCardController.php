<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonthCardConfig;
use App\Models\UserMonthCard;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\MonthCardService;
use App\Service\UserItemService;
use Illuminate\Http\Request;

class MonthCardController extends Controller
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

    /**
     * 取得所有月卡列表及用戶擁有狀態
     */
    public function index(Request $request)
    {
        $user = Users::find(auth()->guard('api')->user()->id);
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $monthCards = MonthCardService::getUserMonthCardStatus($user->uid);

        return response()->json([
            'data' => $monthCards,
        ], 200);
    }

    /**
     * 領取每日獎勵
     */
    public function claimDailyReward(Request $request)
    {
        $user = Users::find(auth()->guard('api')->user()->id);
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $uniqueId = $request->input('unique_id');
        if (empty($uniqueId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MonthCard:0005'), 422);
        }

        $result = MonthCardService::claimDailyReward($user->uid, $uniqueId);

        if (! $result['success']) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 422);
        }

        // 發放每日獎勵道具
        $rewards = MonthCardService::formatRewards($result['daily_reward']);
        $rewardItems = [];

        foreach ($rewards as $reward) {
            $itemResult = UserItemService::addItem(
                1, // TYPE_REWARD
                $user->id,
                $user->uid,
                $reward['item_id'],
                $reward['amount'],
                1,
                '月卡每日獎勵',
                null,
                null,
                null
            );

            if ($itemResult['success']) {
                $rewardItems[] = [
                    'item_id' => $reward['item_id'],
                    'amount' => $reward['amount'],
                ];
            }
        }

        return response()->json([
            'data' => [
                'rewards' => $rewardItems,
            ],
        ], 200);
    }

    /**
     * 重置月卡領取狀態（測試用）
     */
    public function resetMonthlyCardStatus(Request $request)
    {
        // 僅允許測試環境
        $allowedUrls = ['https://project_ai.jengi.tw/api',
            'https://localhost/api',
            'https://laravel.test/api',
            'https://clang-party-dev.wow-dragon.com.tw/api',
            'https://clang_party_dev.wow-dragon.com.tw/api',
            'https://clang-party-qa.wow-dragon.com.tw/api',
        ];

        if (! in_array(config('services.API_URL'), $allowedUrls)) {
            return response()->json(['message' => '限制測試環境使用'], 403);
        }

        $user = Users::find(auth()->guard('api')->user()->id);
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $uniqueId = $request->input('unique_id');
        if (empty($uniqueId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MonthCard:0005'), 422);
        }
        $ary = [1 => 'month001', 2 => 'spmonth001', 3 => 'forever001'];

        $userMonthCard = UserMonthCard::where('uid', $user->uid)
            ->whereHas('config', function ($query) use ($uniqueId, $ary) {
                $query->where('key', $ary[$uniqueId]);
            })
            ->first();

        if (! $userMonthCard) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MonthCard:0002'), 422);
        }

        $userMonthCard->last_daily_reward_at = null;
        $userMonthCard->save();

        return response()->json([
            'data' => [
                'message' => '月卡領取狀態已重置',
            ],
        ], 200);
    }

    /**
     * 購買月卡（測試用）
     */
    public function purchaseMonthCard(Request $request)
    {
        // 僅允許測試環境
        $allowedUrls = [
            'https://project_ai.jengi.tw/api',
            'https://localhost/api',
            'https://laravel.test/api',
            'https://clang-party-dev.wow-dragon.com.tw/api',
            'https://clang_party_dev.wow-dragon.com.tw/api',
            'https://clang-party-qa.wow-dragon.com.tw/api',
        ];

        if (! in_array(config('services.API_URL'), $allowedUrls)) {
            return response()->json(['message' => '限制測試環境使用'], 403);
        }

        $user = Users::find(auth()->guard('api')->user()->id);
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $uniqueId = $request->input('unique_id');
        if (empty($uniqueId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MonthCard:0005'), 422);
        }
        $ary = [1 => 'month001', 2 => 'spmonth001', 3 => 'forever001'];
        // 檢查月卡是否存在
        $monthCardConfig = MonthCardConfig::where('key', $ary[$uniqueId])->first();
        if (empty($monthCardConfig)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MonthCard:0001'), 422);
        }

        // 檢查用戶是否已擁有該月卡
        $existingMonthCard = UserMonthCard::where('uid', $user->uid)
            ->where('month_card_config_id', $monthCardConfig->id)
            ->first();

        $r = MonthCardService::purchaseMonthCard($user, $monthCardConfig);
        if (! $r['success']) {
            return response()->json(ErrorService::errorCode(__METHOD__, $r['error_code']), 422);
        }

        return response()->json([
            'data' => [
                'message' => '月卡購買成功',
                'month_card' => [
                    'key' => $monthCardConfig->key,
                    'expire_at' => $r['expire_at'],
                    'total_purchase_times' => $r['total_purchase_times'],
                ],
            ],
        ], 200);
    }
}
