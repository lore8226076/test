<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewEcpayOrder;
use App\Models\NewEcpayPayment;
use App\Models\Users;
use Ecpay\Sdk\Factories\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewEcpayController extends Controller
{
    protected $key;
    protected $iv;

    public function __construct(Request $request)
    {
        $this->key = config('services.newecpay.HASH_KEY');
        $this->iv  = config('services.newecpay.HASH_IV');

        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['preparePurchase', 'notify', 'checkNewebPay', 'checkEcpay']]);
        }

    }
    public function preparePurchase(Request $request)
    {
        $userUid  = $request->input('user_uid');
        $amount   = intval($request->input('price'));
        $itemDesc = $request->input('item_desc') ?? '商品說明';

        // 查 user 資料
        $user = Users::where('uid', $userUid)->first();
        if (! $user) {
            return response()->json(['message' => '查無此使用者'], 404);
        }

        $orderNo = $this->generateOrderId();

        // 建立本地訂單
        NewEcpayOrder::create([
            'order_no'  => $orderNo,
            'user_id'   => $user->id,
            'amount'    => $amount,
            'item_desc' => $itemDesc,
            'email'     => $user->email,
            'status'    => 'pending',
            'paid_at'   => null,
        ]);

        $factory = new Factory([
            'hashKey' => $this->key,
            'hashIv'  => $this->iv,
        ]);

        $autoSubmitFormService = $factory->create('AutoSubmitFormWithCmvService');

        $actionUrl = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5';

        $input = [
            'MerchantTradeNo'   => $orderNo,
            'MerchantTradeDate' => now()->format('Y/m/d H:i:s'),
            'PaymentType'       => 'aio',
            'TotalAmount'       => $amount,
            'TradeDesc'         => '訂單付款',
            'ItemName'          => $itemDesc,
            'ReturnURL'         => route('ecpay.notify'),
            'ClientBackURL'     => 'https://wow-dragon.com/game-store',
            'OrderResultURL'    => 'https://wow-dragon.com/ecPayCheckPayment',
            'NeedExtraPaidInfo' => 'Y',
            'EncryptType'       => 1,
            'ChoosePayment'     => 'ALL',
            'Email'             => $user->email,
            'MerchantID'        => config('services.newecpay.MERCHANT_ID'),
        ];

        $html = $autoSubmitFormService->generate($input, $actionUrl);
        return response()->json([
            'html'      => $html,
            'order_no'  => $orderNo,
            'amount'    => $amount,
            'item_desc' => $itemDesc,
            'email'     => $user->email,
        ]);
    }

    // ECPay 付款通知
    public function notify(Request $request)
    {
        $data = $request->all();

        $orderNo = $data['MerchantTradeNo'] ?? null;
        $amount  = $data['TradeAmt'] ?? null;
        $status  = $data['RtnCode'] ?? null;
        $tradeNo = $data['TradeNo'] ?? null;
        $method  = $data['PaymentType'] ?? null;

        // 找訂單
        $order = NewEcpayOrder::where('order_no', $orderNo)->first();
        if (! $order) {
            \Log::warning('找不到訂單', ['order_no' => $orderNo]);
            return response('0|Order Not Found', 404);
        }

        // 驗證金額
        if (intval($order->amount) !== intval($amount)) {
            \Log::error('金額不一致', [
                'order_no' => $order->order_no,
                'expected' => $order->amount,
                'received' => $amount,
            ]);
            return response('0|Amount Mismatch', 400);
        }

        // 只處理成功付款
        if ($status == '1' && $order->status !== 'paid') {
            $order->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            NewEcpayPayment::create([
                'ecpay_order_id'    => $order->id,
                'method'            => strtolower($method),
                'amount'            => $amount,
                'status'            => 'success',
                'trade_no'          => $tradeNo,
                'merchant_order_no' => $orderNo,
                'bank_code'         => $data['BankCode'] ?? null,
                'code_no'           => $data['CodeNo'] ?? null,
                'expire_date'       => $data['ExpireDate'] ?? null,
                'paid_at'           => now(),
                'raw_response'      => json_encode($data),
            ]);
            // 可在這裡加發獎勵邏輯
        }

        return response('1|OK');
    }

    // 檢查是否付款成功
    public function checkEcpay(Request $request)
    {
        $orderNo = $request->input('MerchantTradeNo');
        if (! $orderNo) {
            return response()->json(['success' => false, 'message' => '缺少訂單編號'], 400);
        }
        $order = NewEcpayOrder::where('order_no', $orderNo)->first();
        if (! $order) {
            return response()->json(['success' => false, 'message' => '查無此訂單'], 404);
        }
        if ($order->status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => '付款成功',
                'data'    => [
                    // 'reward' => $order->reward_data, // 若有獎勵資料可回傳
                ],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => '尚未付款完成',
            ]);
        }
    }

    // 訂單編號產生器
    public function generateOrderId($prefix = 'EC')
    {
        $datetime = now()->format('YmdHi');
        $random   = strtoupper(Str::random(4));
        return $prefix . $datetime . $random;
    }
}
