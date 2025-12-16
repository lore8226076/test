<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ImportGacha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-gacha';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to import gacha data from txt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $this->info("清空當前資料表 gddb_items ");
        // DB::table('gddb_items')->truncate();
        $gachas = Storage::disk('local')->path('gachas/gachas.txt');
        $gacha_details = [
            'gacha_s00' => Storage::disk('local')->path('gachas/gachaS00.txt'),
            'gacha_s01' => Storage::disk('local')->path('gachas/gachaS01.txt'),
            'gacha_s02' => Storage::disk('local')->path('gachas/gachaS02.txt'),
            'gacha_s03' => Storage::disk('local')->path('gachas/gachaS03.txt'),
        ];
        // 取得扭蛋機資料
        $gachaData = $this->parseGachaFile($gachas);
        $this->insertGachaData($gachaData);
        $this->info("寫入扭蛋機資料完成");

        // 取得扭蛋機詳細資料
        DB::table('gacha_details')->truncate();
        foreach ($gacha_details as $key => $value) {
            $this->info("讀取扭蛋機詳細資料: " . $value);
            $detailData = $this->parseGachaFile($value);
            $this->insertGachaDetailData($detailData);
        }
        $this->info("寫入扭蛋機詳細資料完成");

        return 0;
    }

    // 檔案轉陣列
    private function parseGachaFile($file_contents)
    {
        $this->info("讀取檔案: " . $file_contents);
        $file_contents = file_get_contents($file_contents);
        if ($file_contents === false) {
            return ['error' => '無法讀取檔案'];
        }

        // 將檔案內容按行分割
        $lines = explode("\n", $file_contents);
        unset($file_contents); // 釋放記憶體

        // 解析標題列，取得欄位名稱
        $header = explode("\t", strtr(array_shift($lines), ["// " => "", "\r" => ""]));

        // 過濾無效數據，處理每一行
        $itemLists = array_map(function ($line) use ($header) {
            $data = explode("\t", strtr($line, ["// " => "", "\r" => ""]));
            return isset($data[0]) && is_numeric($data[0]) ? array_combine($header, $data) : null;
        }, $lines);

        // 過濾掉 `null` 值，避免無效數據進入陣列
        return array_filter($itemLists);
    }

    // 寫入資料庫
    private function insertGachaData($datas)
    {
        // 清空gachas資料表
        DB::table('gachas')->truncate();
        foreach ($datas as $data) {
            DB::table('gachas')->insert([
                'id' => $data['id'],
                'name' => $data['name'],
                'localization_name' => 'x',
                'start_time' => $data['start_time'] == 'x' ? null : $data['start_time'],
                'end_time' => $data['end_time'] == 'x' ? null : $data['end_time'],
                'currency_item_id' => $data['currency_item_id'],
                'one_price' => $data['one_price'],
                'ten_price' => $data['ten_price'],
                'is_active' => $data['is_active'],
                'max_times' => $data['max_times'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    // 寫入資料庫
    private function insertGachaDetailData($datas)
    {
        foreach ($datas as $item) {
            $data = DB::table('gacha_details')->insert([
                'gacha_id' => $item['gacha_id'],
                'item_id' => $item['item_id'],
                'percent' => $item['percent'],
                'guaranteed' => $item['rarity'] == 'SSR' ? '1' : '0',
                'qty' => $item['qty'],
                'created_at' => now(),  
                'updated_at' =>now()
            ]);
        }
    }
}
