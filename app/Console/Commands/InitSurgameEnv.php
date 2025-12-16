<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


class InitSurgameEnv extends Command
{
    protected $signature = 'app:init-surgame-env';
    protected $description = '執行Surgame環境初始化設定，會跑相關Seeders';

    public function handle()
    {
        $initSeederAry = [
            'PlayerRegisterInboxMessageSeeder',
            'MonthCardsSeeder',
            'DefaultInboxMessageSeeder',
        ];

        foreach ($initSeederAry as $seederClass) {
            $fqcn = "Database\\Seeders\\{$seederClass}";

            if (!class_exists($fqcn)) {
                $this->warn("跳過Seeder（找不到對應Class）: {$fqcn}");
                continue;
            }

            $this->info("執行Seeder: {$fqcn}");
            $this->call('db:seed', ['--class' => $fqcn]);
        }
    }
}
