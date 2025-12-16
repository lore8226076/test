<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use App\Models\Users;
use App\Service\ErrorService;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    public function __construct(Request $request)
    {
        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['']]);
        }

                                                                           // throtattling
        $this->middleware('throttle:report-by-uid')->only(['reportUser']); // 限制每分鐘最多 10 次請求
    }
    // 檢舉使用者
    public function reportUser(Request $request, string $reportedUid)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        if (empty($reportedUid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        // 檢查被檢舉者是否存在
        $reportedUser = Users::where('uid', $reportedUid)->first();
        if (empty($reportedUser)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $type = $request->input('type', '99');

        $report = UserReport::create([
            'reporter_uid' => $uid,
            'reported_uid' => $reportedUser->uid,
            'type'         => $type,
            'reason'       => $request->input('reason', '尚未填寫'),
            'status'       => 'pending',
            'reported_at'  => now(),
        ]);

        $this->sendDiscordWebhook("有使用者 {$uid} 檢舉了 {$reportedUid}，理由: " . $request->input('reason', '尚未填寫'));
        return response()->json(['data' => [
            'report_id' => $report->id,
            'message'   => '舉報成功！',
        ]], 200);
    }

    private function sendDiscordWebhook($message)
    {
        $webhookUrl = 'https://discord.com/api/webhooks/1369863395834331318/PeQG0FBq-fipLMXq0RRDalutnZZE7SvCt-B_9fgF9Pg3kYf8TiQBBr-zl0H1rbiKoGge'; // 換成你的 webhook URL

        $payload = json_encode([
            'content' => $message,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhookUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            \Log::error('Discord webhook error: ' . curl_error($ch));
        }
        curl_close($ch);
    }
}
