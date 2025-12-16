<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // 執行seeder
        $this->importJsonData();
    }

    private function importJsonData()
    {
        $jsonPath = database_path('jsons');

        if (! File::exists($jsonPath)) {
            $this->command->warn("JSON 資料夾不存在，請確認是否有相關資料。");
            return;
        }

        $files = File::files($jsonPath);
        foreach ($files as $file) {
            $tableName = pathinfo($file, PATHINFO_FILENAME); // 取得資料表名稱
            $jsonData  = json_decode(File::get($file), true);

            if (! empty($jsonData)) {
                DB::table($tableName)->truncate(); // 清空原有資料
                DB::table($tableName)->insert($jsonData);
                $this->command->info("資料表 `$tableName` 已成功填充！");
            }
        }

        $this->command->info("所有 JSON 資料已成功匯入資料庫！");
    }
}
