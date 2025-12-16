<?php

namespace App\Console\Commands;

use App\Models\GddbCharLevels;
use App\Models\GddbItems;
use App\Models\GddbLocalizationName;
use App\Models\GddbNpcs;
use App\Models\GddbPetLevel;
use App\Models\GddbSkills;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ImportItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '將 item JSON 存入資料庫';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('開始匯入資料');
        $this->migrateItemData();
        // $this->migratePetLevelData();
        // $this->migrateLocalizationData();
        // $this->migrateCharLevelData();
        // $this->migrateSkillsData();
        // $this->migrateNpcData();
        // 新增的匯入
        $this->info('資料匯入成功!');

        return 0;
    }

    private function migrateItemData()
    {
        $this->info('開始匯入 Item Data 的資料');
        // 取得env APP_ENV 函式

        $env = env('APP_ENV', 'local');

        if ($env == 'local') {
            $this->info('Local 環境，使用 Dev 資料');
            $item_data_url = 'https://r2dev.wow-dragon.com/gddatabase/04_itemdata.txt';
        } elseif ($env == 'qa') {
            $this->info('QA 環境，使用 QA 資料');
            $item_data_url = 'https://r2qa.wow-dragon.com/gddatabase/04_itemdata.txt';
        } else {
            $this->info('Production 環境，使用 Production 資料');
            $item_data_url = 'https://r2.wow-dragon.com/gddatabase/04_itemdata.txt';
        }

        $datas = $this->convertData($item_data_url, 'txt');

        $this->info('清空當前資料表 gddb_items ');
        GddbItems::truncate();
        $this->info('開始匯入 Item Data 的資料');
        foreach ($datas as $data) {
            GddbItems::create([
                'item_id' => $data['Item ID'],
                'localization_name' => $data['LocalizationName'],
                'localization_description' => $data['LocalizationDescription'],
                'category' => $data['Category'],
                'type' => $data['Type'],
                'style' => $data['Style'],
                'price' => (int) $data['Price'],
                'exchangable' => $data['Exchangable'] === 'TRUE',
                'manager_id' => (int) $data['ManagerId'],
                'network' => $data['Network'] === 'TRUE',
                'npc_id' => (int) $data['Npc ID'],
                'sort_weight' => (int) $data['SortWeight'],
                'show' => $data['Show'] === 'TRUE',
                'subtype' => $data['Subtype'],
                'auto_gen' => $data['AutoGen'] === 'TRUE',
                'region' => $data['Region'],
                'rarity' => $data['Rarity'],
            ]);
        }

        // 清除快取
        Cache::forget('item_data_cache');

        return 0;
    }

    private function migrateCharLevelData()
    {
        // GddbCharLevels::truncate();
        // $item_data_url = 'https://r2.wow-dragon.com/gddatabase/03_char_level_data.txt';
        // $datas         = $this->convertData($item_data_url, 'txt');
        // foreach ($datas as $data) {
        //     GddbCharLevels::create([
        //         'lv'  => $data['lv'],
        //         'exp' => $data['exp'],
        //         'hp'  => $data['hp'],
        //         'atk'  => $data['atk'],
        //         'def' => $data['def'],
        //         'sta' => $data['sta'],
        //         'atk_c' => $data['atk_c'],
        //         'def_c' => $data['def_c'],
        //         'sta_c' => $data['sta_c'],
        //     ]);
        // }
        return 0;
    }

    private function migrateSkillsData()
    {
        // foreach ($datas as $data) {
        //     GddbSkills::create([
        //         'skill_id'              => $data['SkillId'],
        //         'name'                  => $data['Name'],
        //         'desc'                  => $data['Desc'],
        //         'ui_sprite_name'        => $data['UISpriteName'],
        //         'cool_down'             => $data['Cool down'],
        //         'anim_index'            => $data['Anim Index'],
        //         'active_delay'          => $data['Active Delay'],
        //         'active_timeout'        => $data['Active Timeout'],
        //         'distance'              => $data['Distance'],
        //         'protection_time'       => $data['Protection Time'],
        //         'hit_reaction'          => $data['Hit Reaction'],
        //         'hit_dir_mode'          => $data['Hit Dir Mode'],
        //         'knockup_speed_xz'      => $data['Knockup Speed XZ'],
        //         'knockup_speed_y'       => $data['Knockup Speed Y'],
        //         'damage'                => $data['Damage'],
        //         'minimum_damage_limit'  => $data['Minimum Damage Limit'] === 'TRUE',
        //         'minimum_damage_value'  => $data['Minimum Damage Value'],
        //         'proj_attach_on_bone'   => $data['Proj Attach OnBone'] === 'TRUE',
        //         'proj_attach_bone_name' => $data['Proj Attach BoneName'],
        //         'proj_prefab'           => $data['Proj Prefab'],
        //         'proj_explosion_damage' => $data['Proj Explosion Damage'],
        //         'proj_tracking_mode'    => $data['Proj Tracking Mode'],
        //         'parabola'              => $data['Parabola'] === 'TRUE',
        //         'proj_destory_skill_id' => $data['Proj Destory Skill Id'],
        //         'proj_destory_npc_id'   => $data['Proj Destory Npc Id'],
        //     ]);
        // }

        return 0;
    }

    private function migrateNpcData()
    {
        // $this->info("開始匯入 Npc Data 的資料");
        // $this->info("清空當前資料表 gddb_npcs ");
        // GddbNpcs::truncate();
        // $item_data_url = 'https://r2.wow-dragon.com/gddatabase/02_npc_data.txt';
        // $datas         = $this->convertData($item_data_url, 'txt');
        // foreach ($datas as $data) {
        //     GddbNpcs::create([
        //         'enemy_id'       => (int) $data['//id'],
        //         'prefab'         => $data['prefab'],
        //         'search_dist'    => (float) $data['search dist'],
        //         'strafe_min'     => (float) $data['strafe min'],
        //         'strafe_max'     => (float) $data['strafe max'],
        //         'hp'             => (int) $data['hp'],
        //         'bp'             => (int) $data['sta'],
        //         'atk'            => (float) $data['atk'],
        //         'def'            => (int) $data['def'],
        //         'brk'            => (int) $data['brk'],
        //         'lv'             => (int) $data['lv'],
        //         'exp'            => (int) $data['exp'],
        //         'gold'           => (int) $data['gold'],
        //         'skills'         => $data['skills'],
        //         'trigger_radius' => (float) $data['trigger radius'],
        //         'killedscore'    => (int) $data['killedscore'],
        //     ]);
        // }
        return 0;
    }

    private function migratePetLevelData()
    {
        $this->info('開始匯入 Pet Level Data 的資料');
        $this->info('清空當前資料表 gddb_pet_levels ');
        GddbPetLevel::truncate();
        $item_data_url = 'https://r2.wow-dragon.com/gddatabase/05_pet_level_data.txt';
        $datas = $this->convertData($item_data_url, 'txt');
        foreach ($datas as $data) {
            GddbPetLevel::create([
                'lv' => $data['lv'],
                'exp' => $data['exp'],
                'cost' => $data['cost'],
            ]);
        }

        $this->info('Pet Level Data 的資料匯入成功!');

        return 0;
    }

    private function migrateLocalizationData()
    {
        $this->info('開始匯入 Localization Data 的資料');
        $this->info('清空當前資料表 gddb_localization_names ');
        GddbLocalizationName::truncate();

        $item_data_url = 'https://r2.wow-dragon.com/gddatabase/Localization.csv';
        $datas = $this->convertData($item_data_url, 'csv');
        foreach ($datas as $data) {
            if ($data['key'] == 'KEY') {
                continue;
            }
            GddbLocalizationName::create([
                'key' => $data['key'],
                'en_info' => $data['en_info'],
                'zh_info' => $data['zh_info'],
            ]);
        }

        $this->info('Localization Data 的資料匯入成功!');

        return 0;
    }

    // 轉換資料
    private function convertData($item_data_url, $data_type = 'txt')
    {
        // 讀取檔案內容
        $file_contents = file_get_contents($item_data_url);
        if ($file_contents === false) {
            return ['error' => '無法讀取檔案'];
        }

        if ($data_type == 'txt') {
            $lines = explode("\n", $file_contents);
            unset($file_contents);

            // 過濾空行
            $lines = array_filter(array_map('trim', $lines));

            // 如果前兩行都是 // 註解，就丟掉第一行
            if (
                isset($lines[0]) && strpos($lines[0], '//') === 0 &&
                isset($lines[1]) && strpos($lines[1], '//') === 0
            ) {
                array_shift($lines);
            }

            // 取 header，並去掉開頭的 //
            $headerLine = array_shift($lines);
            $headerLine = preg_replace('/^\/\/\s*/', '', $headerLine);
            $header = str_getcsv($headerLine, "\t");

            $datas = [];
            foreach ($lines as $line) {
                $data = str_getcsv($line, "\t");
                $data = array_pad($data, count($header), null);
                $data = array_slice($data, 0, count($header));

                if (! isset($data[0]) || ! is_numeric($data[0])) {
                    continue;
                }

                $row = array_combine($header, $data);
                $datas[$data[0]] = $row;
            }
        }

        if ($data_type == 'csv') {
            // 將檔案內容按行分割
            $lines = explode("\r\n", $file_contents);
            unset($file_contents); // 釋放記憶體
            foreach ($lines as $line) {
                $data = explode(',', $line);
                // key,en_info,zh_info
                $datas[] = [
                    'key' => $data[0],
                    'en_info' => $data[1] ?? null,
                    'zh_info' => $data[2] ?? null,
                ];
            }
        }

        return $datas;
    }
}
