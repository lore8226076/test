<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewebpayOrder;
use App\Models\NewebpayPayment;
use App\Models\Users;
use App\Service\UserItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewEbpayController extends Controller
{
    protected $key;

    protected $iv;

    public function __construct(Request $request)
    {
        $this->key = config('services.newebpay.HASH_KEY');
        $this->iv = config('services.newebpay.HASH_IV');

        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['preparePurchase', 'notify', 'checkNewebPay']]);
        }

    }

    public function preparePurchase(Request $request)
    {
        $userUid = $request->input('user_uid');
        $amount = intval($request->input('price'));
        $productId = $request->input('product_id');
        $itemDesc = $request->input('item_desc');

        // 查 user
        $user = Users::where('uid', $userUid)->first();
        if (! $user) {
            \Log::warning('[Newebpay] 查無使用者', ['user_uid' => $userUid]);

            return response()->json(['message' => '查無此使用者'], 404);
        }

        $merchantID = config('services.newebpay.MERCHANT_ID');
        $time = time();
        $orderNo = $this->generateOrderId('WEB', $userUid);

        // 建立本地訂單 (pending)
        $order = NewebpayOrder::create([
            'order_no' => $orderNo,
            'user_id' => $user->id,
            'amount' => $amount,
            'item_desc' => $itemDesc,
            'email' => $user->email,
            'status' => 'pending',
            'paid_at' => null,
        ]);

        NewEbpayPayment::create([
            'new_ebpay_order_id' => $order->id,
            'method' => 'unknown',
            'amount' => $amount,
            'status' => 'pending',  // pending/success/fail
            'merchant_order_no' => $orderNo,
            'trade_no' => null,
            'bank_code' => null,
            'code_no' => null,
            'expire_date' => null,
            'raw_response' => null,
            'raw_trade_info' => null,
            'decoded_payload' => null,
            'paid_at' => null,
        ]);

        // 準備送藍新
        $tradeData = [
            'MerchantID' => $merchantID,
            'RespondType' => 'String',
            'TimeStamp' => $time,
            'Version' => '2.0',
            'MerchantOrderNo' => $orderNo,
            'Amt' => $amount,
            'VACC' => '0',
            'EZPALIPAY' => '0',
            'WEBATM' => '0',
            'CVS' => '0',
            'CREDIT' => '1',
            'ItemDesc' => $itemDesc,
            'Email' => $user->email,
            'ReturnURL' => config('services.newebpay.return_url'),
            'NotifyURL' => config('services.newebpay.notify_url'),
            'ClientBackURL' => config('services.newebpay.client_back_url'),
        ];

        $queryString = http_build_query($tradeData);
        $tradeData = $this->encrypt($queryString);

        // 回傳前端
        $response = [
            'MerchantID' => $merchantID,
            'TradeInfo' => $tradeData['TradeInfo'],
            'TradeSha' => $tradeData['TradeSha'],
            'Version' => '2.0',
            'PayGateWay' => config('services.newebpay.PAY_GATEWAY'),
        ];


        return response()->json($response);
    }

    public function notify(Request $request)
    {

        $tradeInfo = $request->input('TradeInfo');
        $tradeSha = $request->input('TradeSha');
        $status = $request->input('Status');
        $merchantID = env('NEWEBPAY_MERCHANT_ID');
        $hashKey = $this->key;
        $hashIV = $this->iv;

        // === 狀態檢查 ===
        if (($status ?? '') !== 'SUCCESS') {
            \Log::info('[Newebpay][Notify] 交易失敗通知 (Top-level Status)', ['data' => $request->all()]);

            return response('0|Payment Failed', 400);
        }

        // === 驗證 TradeSha ===
        $localSha = strtoupper(hash('sha256', "HashKey={$hashKey}&{$tradeInfo}&HashIV={$hashIV}"));
        if ($localSha !== $tradeSha) {
            \Log::warning('[Newebpay][Notify] TradeSha 驗證失敗', ['expected' => $localSha, 'received' => $tradeSha]);

            return response('0|TradeSha Error', 400);
        }

        // === 解密 ===
        $decrypt = $this->decryptTradeInfo($tradeInfo);
        if (! $decrypt) {
            \Log::warning('[Newebpay][Notify] 解密失敗', ['trade_info' => $tradeInfo]);

            return response('0|Decrypt Error', 400);
        }

        // === 嘗試解析 ===
        $parsedData = [];
        try {
            // 先嘗試當 JSON
            $parsedData = json_decode($decrypt, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // 不是 JSON → 改用 parse_str
                parse_str($decrypt, $parsedData);
            }
        } catch (\Throwable $e) {
            \Log::error('[Newebpay][Notify] 解析失敗', ['error' => $e->getMessage(), 'raw' => $decrypt]);

            return response('0|Parse Error', 400);
        }

        // === 處理 Result 層（Newebpay JSON 版本會包 Result）===
        $data = $parsedData['Result'] ?? $parsedData;

        if (is_string($data)) {
            // 有些版本的 Result 是 JSON 字串，再解一次
            $data = json_decode($data, true);
        }

        if (empty($data)) {
            \Log::error('[Newebpay][Notify] 找不到 Result 欄位', ['parsed' => $parsedData]);

            return response('0|Result Field Missing', 400);
        }

        // === 找訂單 ===
        $order = NewebpayOrder::where('order_no', $data['MerchantOrderNo'] ?? null)->first();
        if (! $order) {
            \Log::warning('[Newebpay][Notify] 找不到訂單', ['order_no' => $data['MerchantOrderNo'] ?? null]);

            return response('0|Order Not Found', 404);
        }

        // === 金額檢查 ===
        if (intval($order->amount) !== intval($data['Amt'] ?? -1)) {
            \Log::error('[Newebpay][Notify] 金額不一致', [
                'order_no' => $order->order_no,
                'expected' => $order->amount,
                'received' => $data['Amt'] ?? null,
            ]);

            return response('0|Amount Mismatch', 400);
        }

        // === 找或建立 payment ===
        $payment = NewEbpayPayment::where('merchant_order_no', $data['MerchantOrderNo'])->first();
        if (! $payment) {
            $payment = NewEbpayPayment::create([
                'new_ebpay_order_id' => $order->id,
                'method' => strtolower($data['PaymentType'] ?? 'unknown'),
                'amount' => intval($data['Amt'] ?? 0),
                'status' => 'pending',
                'merchant_order_no' => $data['MerchantOrderNo'],
            ]);
        }

        // === 依據訂單發放獎勵 ===
        $this->issueRewards($order, $payment);

        // === 更新狀態 ===
        if ($order->status !== 'paid' || $payment->status !== 'success') {
            $payTime = $data['PayTime'] ?? null;
            $paidAt = $payTime ? \Carbon\Carbon::parse($payTime) : now();

            $payment->update([
                'method' => strtolower($data['PaymentType'] ?? $payment->method),
                'amount' => intval($data['Amt'] ?? $payment->amount),
                'status' => 'success',
                'trade_no' => $data['TradeNo'] ?? $payment->trade_no,
                'bank_code' => $data['BankCode'] ?? $payment->bank_code,
                'code_no' => $data['CodeNo'] ?? $payment->code_no,
                'expire_date' => $data['ExpireDate'] ?? $payment->expire_date,
                'raw_response' => json_encode($request->all(), JSON_UNESCAPED_UNICODE),
                'raw_trade_info' => $request->input('TradeInfo'),
                'decoded_payload' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'paid_at' => $paidAt,
            ]);

            if ($order->status !== 'paid') {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => $paidAt,
                ]);
            }

        } else {
            \Log::info('[Newebpay][Notify] 重複通知已略過（已為 paid/success）', ['order_no' => $order->order_no]);
        }

        return response('1|OK');
    }

    // 檢查是否付款成功（給前端查詢）
    public function checkNewebPay(Request $request)
    {

        $tradeInfo = $request->input('TradeInfo');
        if (! $tradeInfo) {
            return response()->json(['success' => false, 'message' => '缺少 TradeInfo'], 400);
        }

        // === 解密 TradeInfo ===
        $decrypted = $this->decryptTradeInfo($tradeInfo);
        if (! $decrypted) {
            \Log::warning('CheckNewebPay: 解密失敗', ['trade_info' => $tradeInfo]);

            return response()->json(['success' => false, 'message' => '解密失敗'], 400);
        }

        // === 嘗試解析 ===
        $parsedData = [];
        parse_str($decrypted, $parsedData);

        // === 有些版本包 Result（JSON 格式）===
        $result = $parsedData['Result'] ?? $parsedData;
        if (is_string($result)) {
            $decoded = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $result = $decoded;
            }
        }

        $orderNo = $result['MerchantOrderNo'] ?? null;
        if (! $orderNo) {
            \Log::warning('CheckNewebPay: 無法取得 MerchantOrderNo', ['parsed' => $parsedData]);

            return response()->json(['success' => false, 'message' => '找不到訂單編號'], 400);
        }

        // === 查詢付款紀錄 ===
        $payment = NewEbpayPayment::where('merchant_order_no', $orderNo)->first();

        if (! $payment) {
            $existsOrder = NewebpayOrder::where('order_no', $orderNo)->exists();

            return response()->json([
                'success' => false,
                'message' => $existsOrder ? '尚未付款完成' : '查無此訂單',
            ], $existsOrder ? 200 : 404);
        }

        // === 回傳狀態 ===
        return response()->json([
            'success' => $payment->status === 'success',
            'message' => $payment->status === 'success' ? '付款成功' :
                         ($payment->status === 'fail' ? '付款失敗' : '尚未付款完成'),
            'order_no' => $orderNo,
            'status' => $payment->status,
        ]);
    }

    public function encrypt($data)
    {
        $edata = bin2hex(openssl_encrypt($data, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $this->iv));
        $hashs = 'HashKey='.$this->key.'&'.$edata.'&HashIV='.$this->iv;
        $hash = strtoupper(hash('sha256', $hashs));

        return ['TradeInfo' => $edata, 'TradeSha' => $hash];
    }

    // 解密 (HEX to RAW + remove padding)
    public function decryptTradeInfo($tradeInfo)
    {
        $tradeInfo = hex2bin($tradeInfo); // HEX 轉 binary
        $decrypted = openssl_decrypt(
            $tradeInfo,
            'AES-256-CBC',
            $this->key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $this->iv
        );

        // 去掉 Zero Padding
        $decrypted = rtrim($decrypted, "\x00");

        return $decrypted;
    }

    public function generateOrderId($prefix, $uid)
    {
        $datetime = now()->format('YmdHi');     // 202507221530
        $random = strtoupper(Str::random(4)); // A1B2

        return $uid.'_'.$datetime.'_'.$random;
    }

    private function issueRewards($order, $payment)
    {
        $usvc = app(UserItemService::class);
        $itemId = 100; // 星環幣 ID
        $user = Users::find($order->user_id);
        if (! $user) {
            \Log::error('[Newebpay][Notify] 發放商品失敗，找不到使用者', ['user_id' => $order->user_id, 'order_no' => $order->order_no]);
            return;
        }

        try {
            $usvc->addItem(11, $user->id, $user->uid, $itemId, $order->amount, 1, '官網商城藍新支付購買道具, 道具item_id = '.$itemId . ', 數量 = ' . $order->amount);
        } catch (\Throwable $e) {
            \Log::error('[Newebpay][Notify] 商品發放失敗', [
                'order_no' => $order->order_no,
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function strippadding($string)
    {
        $slast = ord(substr($string, -1));
        $slastc = chr($slast);
        $pcheck = substr($string, -$slast);
        if (preg_match("/$slastc{".$slast.'}/', $string)) {
            $string = substr($string, 0, strlen($string) - $slast);

            return $string;
        } else {
            return false;
        }
    }
}
