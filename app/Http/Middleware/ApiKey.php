<?php

namespace App\Http\Middleware;

use Closure;

class ApiKey
{
    protected $except = [
        'api/photon/*',
        'api/payment/notify-ecpay',
        'api/payment/notify-newebpay',
        'api/webhook/line-alert',
        'api/webhook/send-alert',
    ];

    public function handle($request, Closure $next)
    {
        foreach ($this->except as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        $origin = $request->header('Origin');
        $referer = $request->header('Referer');

        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);

        if($referrerDomain  != config('services.API_PASS_DOMAIN')){
            $apiKey = $request->header('x-api-key');

            // 检查是否包含 x-api-key 头
            if (!$apiKey) {
                return response()->json(['error' => 'Missing x-api-key header'], 401);
            }

            // 在此添加您的 x-api-key 验证逻辑
            if ($apiKey !== config('services.API_KEY')) {
                return response()->json(['error' => 'Invalid API key'], 401);
            }
        }
        return $next($request);
    }
}
