<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErrorController extends Controller
{
    /**
     * 接收 LINE Webhook (用來記錄群組ID)
     */
    public function LineWebhook(Request $request)
    {
        try {
            $signature = $request->header('x-line-signature');
            $body = $request->getContent();
            $channelSecret = env('LINE_CHANNEL_SECRET');
            $hash = base64_encode(hash_hmac('sha256', $body, $channelSecret, true));

            if ($signature !== $hash) {
                Log::channel('line')->warning('LINE 簽章錯誤', [
                    'expected' => $hash,
                    'received' => $signature,
                ]);

                return response('Invalid signature', 200);
            }

            $payload = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::channel('line')->error('JSON 解析失敗', ['body' => $body]);

                return response('Invalid JSON', 200);
            }

            foreach ($payload['events'] ?? [] as $event) {
                $type = $event['type'] ?? '';
                $source = $event['source']['type'] ?? '';
                $groupId = $event['source']['groupId'] ?? null;
                $eventId = $event['eventId'] ?? uniqid('noevent_');

                // 防重 (LINE Redelivery 時)
                if (Cache::has("line_event_$eventId")) {
                    Log::channel('line')->info('忽略重複事件', ['eventId' => $eventId]);

                    continue;
                }
                Cache::put("line_event_$eventId", true, now()->addMinutes(5));

                if ($type === 'join' && $groupId) {
                    Cache::put('line_group_id', $groupId, now()->addDays(7));
                }

                // 如果有人在群組講話，順便記錄 groupId
                if ($type === 'message' && $groupId) {
                    Cache::put('line_group_id', $groupId, now()->addDays(7));
                }
            }

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::channel('line')->error('Webhook 錯誤', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('Webhook error', 200);
        }
    }

    /**
     * 主動推播錯誤通知
     * 你的監控系統或應用可 POST JSON 到這裡
     * {
     *   "service": "web-api",
     *   "level": "ERROR",
     *   "msg": "DB timeout x5",
     *   "host": "host01"
     * }
     */
    public function SendAlert(Request $request)
    {
        $token = env('LINE_CHANNEL_ACCESS_TOKEN');
        $url = 'https://api.line.me/v2/bot/message/push';

        try {
            $payload = $request->all();
            // $groupId = Cache::get('line_group_id');
            $groupId = env('LINE_ALERT_GROUP_ID');

            if (! $groupId) {
                Log::channel('line')->warning('尚未記錄群組ID，無法發送');

                return response()->json(['success' => false, 'message' => 'No groupId'], 200);
            }

            $text = sprintf(
                "[%s] %s\nHost: %s\nTime: %s\nMsg: %s",
                $payload['level'] ?? 'INFO',
                $payload['service'] ?? 'unknown',
                $payload['host'] ?? 'unknown',
                now()->toDateTimeString(),
                $payload['msg'] ?? '(no message)'
            );

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json; charset=UTF-8',
            ])->withOptions([
                'json' => true,
                'verify' => false,
            ])->post($url, [
                'to' => $groupId,
                'messages' => [
                    ['type' => 'text', 'text' => mb_convert_encoding($text, 'UTF-8')],
                ],
            ]);

            if (! $response->successful()) {
                Log::channel('line')->error('LINE Push 發送失敗', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json(['success' => false, 'message' => 'Push failed'], 200);
            }

            return response()->json(['success' => true, 'message' => 'Alert sent'], 200);

        } catch (\Throwable $e) {
            Log::channel('line')->error('發送錯誤', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Exception occurred'], 200);
        }
    }
}
