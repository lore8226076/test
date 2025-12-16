<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckPorts extends Command
{
    /**
     * 指令名稱： php artisan check:ports
     */
    protected $signature = 'check:ports';

    protected $description = 'Check MySQL(3306) and API(8080) ports, send LINE alert if down';

    public function handle()
    {
        $ports = [
            ['service' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306],
            ['service' => 'laravel-api', 'host' => '127.0.0.1', 'port' => 8080],
        ];

        foreach ($ports as $p) {
            $this->checkPort($p['service'], $p['host'], $p['port']);
        }
    }

    /**
     * 實際檢查 port 是否可連線
     */
    private function checkPort($service, $host, $port)
    {
        $start = microtime(true);
        $fp = @fsockopen($host, $port, $errno, $errstr, 3);
        $duration = round((microtime(true) - $start) * 1000, 2);

        if (! $fp) {
            $msg = "{$service} {$port} 無法連線：{$errstr}";

            $this->error("[失敗] {$msg}");

            // 發 LINE 通知
            Http::post(config('app.url') . '/api/webhook/send-alert', [
                'level' => 'ERROR',
                'service' => $service,
                'host' => gethostname(),
                'msg' => $msg,
            ]);
        }
    }
}
