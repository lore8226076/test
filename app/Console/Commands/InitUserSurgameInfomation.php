<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitUserSurgameInfomation extends Command
{
    protected $signature = 'app:init-user-surgame-infomation {uid : 用戶UID（必填）}';

    protected $description = '初始化用戶遊戲資訊（刪除指定用戶的所有遊戲數據）';

    /**
     * 需要清除的資料表（依據 uid 欄位）
     */
    protected array $tablesToClear = [
        'user_tasks',
        'user_talent_session_logs',
        'user_talent_pool_sessions',
        'user_surgame_resource_stage_records',
        'user_surgame_funcs',
        'user_statuses',
        'user_stats',
        'user_stamina_logs',
        'user_stages',
        'user_slot_equipments',
        'user_patrol_rewards',
        'user_pay_orders',
        'user_login_logs',
        'user_journey_records',
        'user_journey_reward_maps',
        'user_journey_star_challenges',
        'user_journey_star_reward_maps',
        'user_inbox_entries',
        'user_gacha_times',
        'user_gacha_orders',
        'user_equipment_sessions',
        'user_surgame_infos',
        'user_equipment_powers',
        'user_equipment_attributes',
        'user_characters',
        'character_deploy_slots',
        'user_first_purchase_records',
        'user_month_cards',
        'inbox_targets',
    ];

    /**
     * 需要重置為 0 的 users 欄位
     */
    protected array $userFieldsToReset = [
        'teaching_square',
        'teaching_level',
        'teaching_name',
        'teaching_task',
        'teaching_mapeditor',
        'teaching_pet',
        'teaching_levelselector',
        'teaching_maplobby',
        'teaching_gacha',
        'teaching_surgame_intro',
        'teaching_main_stage2',
        'teaching_formation_intro',
        'teaching_rank_intro',
        'teaching_paperdoll_intro',
        'has_edit_map_opening_anim_played',
        'teaching_received_costume_coupon',
        'teaching_performed_costume_gacha',
        'teaching_is_role_upgraded'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uid = $this->argument('uid');

        // 確認操作（透過 --no-interaction 可跳過）
        if (! $this->option('no-interaction') && ! $this->confirm("確定要刪除用戶 {$uid} 的所有遊戲數據嗎？此操作不可逆！")) {
            $this->info('操作已取消。');

            return 0;
        }

        $this->info("開始初始化用戶 {$uid} 的遊戲資訊...");
        $this->newLine();

        $totalSteps = count($this->tablesToClear) + 1; // +1 for users table update
        $bar = $this->output->createProgressBar($totalSteps);
        $bar->start();

        $deletedCounts = [];

        DB::beginTransaction();
        try {
            foreach ($this->tablesToClear as $table) {

                // 指定table target_uid 使用target_uid欄位
                if ($table === 'inbox_targets') {
                    $count = DB::table($table)->where('target_uid', $uid)->count();
                    $deleted = DB::table($table)->where('target_uid', $uid)->delete();
                    if ($deleted !== $count) {
                        $this->warn("資料表 {$table}: 預期刪除 {$count} 筆，實際刪除 {$deleted} 筆");
                    }
                    $deletedCounts[$table] = $deleted;
                    $bar->advance();

                    continue;
                }

                try {
                    $count = DB::table($table)->where('uid', $uid)->count();
                    $deleted = DB::table($table)->where('uid', $uid)->delete();

                    if ($deleted !== $count) {
                        $this->warn("資料表 {$table}: 預期刪除 {$count} 筆，實際刪除 {$deleted} 筆");
                    }

                    $deletedCounts[$table] = $deleted;
                    $bar->advance();
                } catch (\Exception $e) {
                    $this->error("刪除資料表 {$table} 失敗: ".$e->getMessage());
                    throw $e; // 重新拋出以觸發 rollback
                }
            }

            // 重置 users 表的欄位為 0
            $resetData = array_fill_keys($this->userFieldsToReset, 0);
            $userUpdated = DB::table('users')->where('uid', $uid)->update($resetData);
            $bar->advance();

            DB::commit();
            $bar->finish();
            $this->newLine(2);

            // 顯示刪除結果
            $this->info('刪除完成！各資料表刪除筆數：');
            $this->newLine();

            $tableData = [];
            $totalDeleted = 0;
            foreach ($deletedCounts as $table => $count) {
                $tableData[] = [$table, $count];
                $totalDeleted += $count;
            }

            $this->table(['資料表', '刪除筆數'], $tableData);
            $this->newLine();
            $this->info("總共刪除 {$totalDeleted} 筆資料。");
            $this->newLine();

            // 顯示 users 欄位重置結果
            if ($userUpdated) {
                $this->info('已重置 users 表的以下欄位為 0：');
                $this->line(implode(', ', $this->userFieldsToReset));
            } else {
                $this->warn("找不到 UID {$uid} 的用戶，無法重置欄位。");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $bar->finish();
            $this->newLine(2);
            $this->error('初始化失敗：'.$e->getMessage());
            \Log::error('初始化用戶遊戲資訊失敗', [
                'uid' => $uid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }
}
