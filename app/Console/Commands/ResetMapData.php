<?php
namespace App\Console\Commands;

use App\Models\UserMaps;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ResetMapData extends Command
{

    protected $signature = 'reset:map-data';

    protected $description = '重置地圖資料';

    public function handle()
    {
        return false;
        // 如果extend_id 欄位存在
        if (Schema::hasColumn('user_maps', 'extend_id')) {

            // 刪除原本草稿資料
            $maps = UserMaps::where('is_draft', 1)->where('extend_id', '!=', null)->get();
            foreach ($maps as $map) {
                $map->forceDelete();
            }

            // 將原本地圖資料轉為草稿
            $maps = UserMaps::where('is_draft', 0)->get();
            foreach ($maps as $map) {
                // 家園的話直接加上is_draft = 1
                if ($map->is_home == 1) {
                    $map->is_draft = 1;
                    $map->save();
                }
            }

        }
        if (Schema::hasColumn('user_maps', 'draft_id')) {
            {
                // 將原本地圖資料轉為草稿
                $maps = UserMaps::where('is_draft', 0)->where('is_home', 0)->get();
                foreach ($maps as $map) {
                    // 產生不重複的 map_uuid
                    do {
                        $randomNum = rand(100000000000, 999999999999);
                    } while (UserMaps::where('map_uuid', $randomNum)->exists());

                    // 複製地圖資料
                    $newMapData           = $map->replicate();
                    $newMapData->is_draft = 1;
                    $newMapData->map_uuid = $randomNum;
                    $newMapData->save();

                    // 原地圖設 draft_id 和 map_uuid
                    $map->draft_id = $newMapData->id;
                    $map->map_uuid = $randomNum;
                    $map->save();
                }

                // 取得所有家園地圖 給予 map_uuid
                $homeMaps = UserMaps::where('is_home', 1)->get();
                foreach ($homeMaps as $homeMap) {
                     // 產生不重複的 map_uuid
                     do {
                        $randomNum = rand(100000000000, 999999999999);
                    } while (UserMaps::where('map_uuid', $randomNum)->exists());

                    $homeMap->map_uuid = $randomNum;
                    $homeMap->save();
                }
            }
        }
    }

}
