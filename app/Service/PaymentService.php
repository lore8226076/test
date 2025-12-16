<?php

namespace App\Service;

use App\Models\ItemPrices;
use App\Models\MonthCardConfig;
use App\Models\UserFirstPurchaseRecord;
use App\Models\UserItemLogs;
use App\Models\UserPayOrders;
use App\Models\Users;
use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * 核心處理流程：驗證 -> 檢查重複 -> 建單 -> 發貨
     * 前端 (Unity) 呼叫 createOrder 時，實際執行的是這個流程
     *
     * @param  Users  $user  當前用戶
     * @param  array  $data  包含 payment_method, purchase_token, product_id, amount, currency
     */
    public function processOrder(Users $user, array $data)
    {
        $platform = $data['payment_method'];
        $token = $data['purchase_token']; // Apple=Receipt, Google=Token
        $productId = $data['product_id'];

        // ==========================================
        // Step 1: 呼叫官方 API 驗證憑證
        // ==========================================
        // 這裡將 purchase_token 同時傳入 token 與 receipt_data，讓底層自動判斷
        $verifyResult = self::verifyPurchase($platform, [
            'purchase_token' => $token,
            'receipt_data' => $token,
            'product_id' => $productId,
        ]);

        // 檢查 API 連線與基礎驗證狀態
        if (($verifyResult['status'] ?? -1) === 'failed') {
            return $verifyResult;
        }

        // Apple 特別檢查：status 必須為 0 才算成功
        if ($platform === 'apple' && ($verifyResult['status'] ?? -1) !== 0) {
            return ['status' => 'failed', 'error' => 'Apple receipt invalid: '.($verifyResult['status'] ?? 'unknown')];
        }

        // ==========================================
        // Step 2: 提取官方唯一的 Transaction ID (防止重刷)
        // ==========================================
        $transactionId = null;
        $purchaseTime = now();
        $targetTransaction = []; // Apple 用

        if ($platform === 'apple') {
            // 解析 Apple 收據陣列，找出該商品的最新一筆交易
            $inAppArr = $verifyResult['receipt']['in_app'] ?? [];
            $targetTransaction = $this->findLatestTransaction($inAppArr, $productId);

            if (! $targetTransaction) {
                return ['status' => 'failed', 'error' => '請檢查 product_id 是否正確: '.$productId];
            }
            $transactionId = $targetTransaction['transaction_id'];
            $purchaseTime = isset($targetTransaction['purchase_date_ms'])
                ? now()->createFromTimestampMs($targetTransaction['purchase_date_ms']) : now();

        } else { // Google
            $transactionId = $verifyResult['orderId'] ?? null;
            $purchaseTime = isset($verifyResult['purchaseTimeMillis'])
                ? now()->createFromTimestampMs($verifyResult['purchaseTimeMillis']) : now();
        }

        if (empty($transactionId)) {
            return ['status' => 'failed', 'error' => '無法從平台取得交易 ID'];
        }

        // ==========================================
        // Step 3: 檢查資料庫是否已存在此訂單 (Idempotency)
        // ==========================================
        $order = UserPayOrders::where('transaction_id', $transactionId)->first();

        if ($order) {
            // 情境 A: 訂單已存在且成功 (前端重送請求) -> 直接回傳成功
            if ($order->status === 'success') {
                Log::info('重複請求已完成的訂單', ['order_id' => $order->order_id, 'uid' => $user->uid]);

                return ['status' => 'success', 'order_id' => $order->order_id, 'is_replay' => true];
            }
            // 情境 B: 訂單存在但失敗或處理中 -> 繼續執行發貨流程
        } else {
            // 情境 C: 訂單不存在 -> 【建立新訂單】
            $order = UserPayOrders::create([
                'user_id' => $user->id,
                'uid' => $user->uid,
                'order_id' => Str::uuid(),
                'transaction_id' => $transactionId,
                'status' => 'pending',
                'amount' => $data['amount'] ?? 0,
                'currency' => $data['currency'] ?? 'TWD',
                'payment_method' => $platform,
                'package_id' => $productId,
                'purchase_token' => substr($token, 0, 1000), // 截斷過長的 token
                'purchase_time' => $purchaseTime,
                'raw_response' => json_encode($verifyResult),
            ]);
        }

        // ==========================================
        // Step 4: 執行發貨與後續處理
        // ==========================================

        // Google 需要確認訂單 (Acknowledge)，否則 3 天後會被退款
        if ($platform === 'google') {
            $this->acknowledgeGoogleOrder($order, $productId, $token, $verifyResult);
        }

        // 發送道具 (原本的邏輯)
        $sendResult = $this->sendItem($user, $productId);

        if ($sendResult['status'] === 'success') {
            // 發貨成功 -> 標記訂單完成
            $order->update([
                'status' => 'success',
                'completed_at' => now(),
            ]);

            return ['status' => 'success', 'order_id' => $order->order_id];
        } else {
            // 發貨失敗 -> 標記訂單失敗 (保留紀錄供客服查詢)
            $order->update([
                'status' => 'failed',
                'error_info' => json_encode($sendResult),
            ]);

            Log::error('發貨失敗', ['order_id' => $order->order_id, 'result' => $sendResult]);

            return $sendResult;
        }
    }

    /**
     * 靜態方法：執行 Google 或 Apple 的 API 驗證
     */
    public static function verifyPurchase(string $platform, array $options)
    {
        Log::info("開始驗證 {$platform} 憑證", [
            'product_id' => $options['product_id'] ?? '',
        ]);

        // Apple 驗證流程
        if ($platform === 'apple') {
            $prodUrl = 'https://buy.itunes.apple.com/verifyReceipt';
            $sandboxUrl = 'https://sandbox.itunes.apple.com/verifyReceipt';

            // 這裡抓取 receipt_data
            $payload = [
                'receipt-data' => $options['receipt_data'] ?? '',
                'password' => config('services.APPLE.SHARED_SECRET'),
                'exclude-old-transactions' => true,
            ];

            // 1. 嘗試 Production
            $response = self::sendAppleRequest($prodUrl, $payload);

            // 2. 如果回傳 21007，轉向 Sandbox
            if (isset($response['status']) && $response['status'] == 21007) {
                Log::info('轉向 Apple Sandbox 驗證');
                $response = self::sendAppleRequest($sandboxUrl, $payload);
            }

            if (! isset($response['status']) || $response['status'] !== 0) {
                return ['status' => 'failed', 'error' => 'apple_receipt_invalid', 'details' => $response];
            }

            return $response;
        }

        // Google 驗證流程
        if ($platform === 'google') {
            $packageName = config('services.GOOGLE_PLAY.PACKAGE_NAME');
            $productId = $options['product_id'];
            $purchaseToken = $options['purchase_token'] ?? '';

            $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/products/{$productId}/tokens/{$purchaseToken}";

            try {
                $accessToken = static::getGooglePlayToken();
                $response = Http::withToken($accessToken)->get($url);

                if ($response->failed()) {
                    Log::error('Google API 驗證失敗', ['body' => $response->body()]);

                    return ['status' => 'failed', 'error' => 'google_verification_failed'];
                }

                $data = $response->json();

                if (empty($data)) {
                    return ['status' => 'failed', 'error' => 'google_response_empty'];
                }

                // 檢查購買狀態 (0 = Purchased)
                if (! isset($data['purchaseState']) || $data['purchaseState'] != 0) {
                    return ['status' => 'failed', 'error' => 'google_order_not_paid', 'details' => $data];
                }

                return $data;

            } catch (\Exception $e) {
                Log::error('Google 驗證例外', ['msg' => $e->getMessage()]);

                return ['status' => 'failed', 'error' => 'google_exception'];
            }
        }

        return ['status' => 'failed', 'error' => 'unsupported_platform'];
    }

    /**
     * Google Acknowledge (確認訂單)
     * 避免 Google 在 3 天後自動退款
     */
    private function acknowledgeGoogleOrder($order, $productId, $purchaseToken, $verifyData)
    {
        // 如果已經確認過 (acknowledgementState = 1)，則跳過
        if (! empty($verifyData['acknowledgementState']) && $verifyData['acknowledgementState'] == 1) {
            return;
        }

        try {
            $packageName = config('services.GOOGLE_PLAY.PACKAGE_NAME');
            $accessToken = static::getGooglePlayToken();

            $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/products/{$productId}/tokens/{$purchaseToken}:acknowledge";

            $response = Http::withToken($accessToken)->post($url, [
                'developerPayload' => '',
            ]);

            if ($response->failed()) {
                Log::warning('Google Acknowledge 失敗', [
                    'order_id' => $order->order_id,
                    'body' => $response->body(),
                ]);
            } else {
                $order->update(['acknowledged_at' => now()]);
            }
        } catch (\Throwable $e) {
            Log::error('Google Acknowledge 例外', ['msg' => $e->getMessage()]);
        }
    }

    /**
     * 發送道具 (包含首儲判斷、月卡判斷)
     */
    protected function sendItem(Users $user, $packageId)
    {
        // 1. 檢查是否為月卡
        $monthCardResult = $this->handleMonthCardPurchase($user, $packageId);
        if ($monthCardResult !== null) {
            return $monthCardResult;
        }

        // 2. 取得一般商品資訊
        $productData = $this->getProduct($packageId);
        if (isset($productData['status']) && $productData['status'] == 'failed') {
            return $productData;
        }

        $productId = $productData['product_id'];
        $itemId = $productData['item_id'];
        $qty = $productData['qty'];

        // 3. 判斷 Log 類型
        $logType = $this->returnPurchaseLogTypeNum($productId);

        // 4. 判斷首儲雙倍
        $isFirstPurchase = UserFirstPurchaseRecord::isFirstPurchase($user->id, $productId);
        $originalQty = $qty;

        if ($isFirstPurchase) {
            $qty = $qty * 2;
        }

        // 5. 寫入道具 (UserItemService)
        $itemResult = UserItemService::addItem($logType, $user->id, $user->uid, $itemId, $qty, 1, '儲值購買');

        if (empty($itemResult['success'])) {
            return ['status' => 'failed', 'error' => $itemResult['error_code'] ?? 'add_item_failed'];
        }

        // 6. 記錄首儲與獎勵發放狀態
        if ($isFirstPurchase) {
            $purchaseRecord = UserFirstPurchaseRecord::recordItemPurchase(
                $user->id,
                $user->uid,
                $productId,
                $itemId,
                true
            );

            $purchaseRecord->markRewardSent([
                'original_qty' => $originalQty,
                'bonus_qty' => $originalQty,
                'total_qty' => $qty,
            ]);
        }

        return [
            'status' => 'success',
            'is_first_purchase' => $isFirstPurchase,
        ];
    }

    // ==========================================
    // 輔助函式區
    // ==========================================

    /**
     * Apple: 找出符合 Product ID 且最新的交易
     */
    private function findLatestTransaction(array $inAppTransactions, string $targetProductId)
    {
        $filtered = array_filter($inAppTransactions, function ($item) use ($targetProductId) {
            return isset($item['product_id']) && $item['product_id'] === $targetProductId;
        });

        if (empty($filtered)) {
            return null;
        }

        // 時間降序 (最新的在前)
        usort($filtered, function ($a, $b) {
            return ($b['purchase_date_ms'] ?? 0) <=> ($a['purchase_date_ms'] ?? 0);
        });

        return $filtered[0];
    }

    /**
     * Apple: 發送 HTTP 請求
     */
    private static function sendAppleRequest($url, $payload)
    {
        try {
            $httpResponse = Http::post($url, $payload);
            if ($httpResponse->failed()) {
                return ['status' => 'failed', 'error' => 'http_error', 'code' => $httpResponse->status()];
            }

            return $httpResponse->json();
        } catch (\Exception $e) {
            Log::error('Apple API 連線異常', ['msg' => $e->getMessage()]);

            return ['status' => 'failed', 'error' => 'connection_exception'];
        }
    }

    /**
     * Google: 取得 API Token
     */
    protected static function getGooglePlayToken()
    {
        $path = storage_path(config('services.GOOGLE_PLAY.SERVICE_ACCOUNT'));
        if (! file_exists($path)) {
            throw new \Exception("找不到服務帳戶金鑰: {$path}");
        }

        $client = new Client;
        $client->setAuthConfig($path);
        $client->addScope('https://www.googleapis.com/auth/androidpublisher');
        $tokenResponse = $client->fetchAccessTokenWithAssertion();

        if (isset($tokenResponse['access_token'])) {
            return $tokenResponse['access_token'];
        }
        throw new \Exception('Google Token 無法取得');
    }

    /**
     * 取得產品資訊 (去除 gp 字首)
     * 處理像 gp0001a, gp0001_ios 這種後綴，還原成 gp0001
     */
    private function getProduct(string $productId)
    {
        // 1. 正規表達式提取核心 ID
        if (preg_match('/(gp\d+)/i', $productId, $matches)) {
            $cleanId = strtolower($matches[1]); // 統一轉小寫，例如 gp0001
        } else {
            $cleanId = $productId;
        }

        // 2. 查詢資料庫
        $data = ItemPrices::where('tag', 'Cash')
            ->where('product_id', $cleanId)
            ->first();

        if (empty($data)) {
            Log::warning('商品不存在', [
                'original_id' => $productId,
                'cleaned_id' => $cleanId,
            ]);

            return ['status' => 'failed', 'error' => '商品不存在', 'product_id' => $cleanId];
        }

        return [
            'status' => 'ok',
            'product_id' => $data->product_id,
            'item_id' => $data->item_id,
            'qty' => $data->qty,
        ];
    }

    /**
     * 判斷 Log 類型
     */
    private function returnPurchaseLogTypeNum($productId)
    {
        // 簡單判斷：如果是鑽石類 (gp0001~0006) 給特定 Log ID
        if (in_array($productId, ['gp0001', 'gp0002', 'gp0003', 'gp0004', 'gp0005', 'gp0006'])) {
            return UserItemLogs::TYPE_BUY_DIAMOND;
        }

        return UserItemLogs::TYPE_ORDER_BUY;
    }

    // ==========================================
    // 月卡相關邏輯 (保持原樣)
    // ==========================================

    protected function handleMonthCardPurchase(Users $user, string $productId): ?array
    {
        $monthCardConfig = MonthCardConfig::where('key', $productId)
            ->where('is_active', 1)
            ->first();

        if (! $monthCardConfig) {
            return null; // 非月卡
        }

        Log::info('處理月卡購買', ['uid' => $user->uid, 'product_id' => $productId]);

        $result = MonthCardService::purchaseMonthCard($user, $monthCardConfig);

        if (! $result['success']) {
            return ['status' => 'failed', 'error' => $result['error_code']];
        }

        if ($result['is_first_purchase']) {
            $purchaseRecord = UserFirstPurchaseRecord::recordMonthCardPurchase(
                $user->id, $user->uid, $productId, $monthCardConfig->id, true
            );

            if (! empty($result['basic_reward'])) {
                $this->sendMonthCardReward($user, $result['basic_reward'], '月卡首購獎勵');
                $purchaseRecord->markRewardSent(['basic_reward' => $result['basic_reward']]);
            }
        }

        return [
            'status' => 'success',
            'type' => 'month_card',
            'month_card_key' => $monthCardConfig->key,
            'is_first_purchase' => $result['is_first_purchase'],
            'expire_at' => $result['expire_at'],
        ];
    }

    protected function sendMonthCardReward(Users $user, array $rewards, string $memo): void
    {
        $formattedRewards = MonthCardService::formatRewards($rewards);
        foreach ($formattedRewards as $reward) {
            UserItemService::addItem(
                12,
                $user->id,
                $user->uid,
                $reward['item_id'],
                $reward['amount'],
                1,
                $memo
            );
        }
    }
}
