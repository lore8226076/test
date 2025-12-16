<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\PaymentService;
use Illuminate\Http\Request;

class UserPayOrderController extends Controller
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
     * 建立訂單 (核心邏輯：驗證收據 -> 建檔 -> 發貨)
     * 前端在獲得 Apple/Google 收據後呼叫此接口
     */
    public function createOrder(Request $request, PaymentService $paymentService)
    {
        $user = Users::find(auth()->guard('api')->id());
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        // 1. 驗證必要參數
        $validated = $request->validate([
            'payment_method' => 'required|string|in:google,apple',
            'product_id' => 'required|string', // 商品ID
            'purchase_token' => 'required|string', // Apple=Receipt, Google=Token
            'amount' => 'nullable|numeric', // 僅做紀錄用 月卡可帶可不帶
            'currency' => 'nullable|string',
        ]);

        \Log::info('收到 createOrder 請求', [
            'uid' => $user->uid,
            'product_id' => $validated['product_id'],
            'method' => $validated['payment_method'],
        ]);

        // 2. 呼叫 Service 執行「一條龍」處理
        $result = $paymentService->processOrder($user, $validated);

        // 3. 處理結果回傳
        if ($result['status'] === 'success') {
            return response()->json([
                'message' => '訂單建立成功',
                'order_id' => $result['order_id'], // 回傳建立好的訂單編號
            ], 200);
        } else {
            \Log::warning('createOrder 處理失敗', ['uid' => $user->uid, 'result' => $result]);

            return response()->json(
                ErrorService::errorCode(__METHOD__, 'UserPayOrder:0003', $result['error'] ?? 'Unknown Error'),
                400
            );
        }
    }
}
