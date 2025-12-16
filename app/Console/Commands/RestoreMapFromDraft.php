<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserMaps;
use Illuminate\Support\Facades\Log;
use App\Service\FileService;


class RestoreMapFromDraft extends Command
{
    protected $signature = 'map:repair {--check=0}';
    protected $description = 'Restore map from draft, or check empty maps only';

    public function handle()
    {
        $onlyCheck = (int) $this->option('check') === 1;

        $userMaps = UserMaps::where('is_publish', 1)->get();

        $emptyMapIds = [];

        foreach ($userMaps as $userMap) {
            if ($userMap->map_data === null || $userMap->map_data === '') {
                if ($onlyCheck) {
                    $emptyMapIds[] = $userMap->id;
                } else {
                    if ($userMap->draft_id) {
                        $draftMap = UserMaps::find($userMap->draft_id);
                        if ($draftMap && $draftMap->map_file_name) {
                            $this->saveMapData($draftMap->user_id, $userMap, $draftMap->map_data);
                            $userMap->save();
                            $this->info("已恢復地圖 #{$userMap->id} 從 draft #{$draftMap->id}");
                        } else {
                            $emptyMapIds[] = $userMap->id;
                            $this->warn("找不到 draft 或 draft 無 map_file_name，map_id: {$userMap->id}");
                        }
                    } else {
                        $emptyMapIds[] = $userMap->id;
                        $this->warn("地圖 #{$userMap->id} 沒有 draft_id，無法恢復");
                    }
                }
            }
        }

        $action = $onlyCheck ? '檢查' : '修復';
        $msg = $emptyMapIds
            ? "{$action}完成，map_data 為空的地圖 ID: " . implode(',', $emptyMapIds)
            : "{$action}完成，所有地圖皆正常";

        Log::info($msg);
        $this->info($msg);
    }

        /** map_data 儲存成檔案 */
    private function saveMapData($userId, $userMap, $map_data)
    {
        // 取得r2路徑
        if (! empty($userMap->map_file_path)) {
            FileService::deleteR2File($userMap->map_file_path);
        }

        $txtContent = $map_data;
        $module     = 'user_maps';
        $file_name  = $userId . '_' . time() . rand(1000, 9999) . '.txt';
        $result     = FileService::upload_string($txtContent, $module, $file_name);
        if (! $result) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MAP:0004'), 500);
        }
        $userMap->map_file_path = $result['file_path'];
        $userMap->map_file_name = $result['file_name'];
    }
}
