<?php
namespace Database\Seeders;

use App\Models\TaskCategory;
use App\Models\Tasks;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    /**
     * repeatable_type = 0: 不可重複
     * repeatable_type = 1: 每日重置
     * repeatable_type = 2: 每週重置
     * repeatable_type = 3: 每月重置
     * repeatable_type = -1: 做完馬上重置
     */
    public function run(): void
    {
        // 清空現有資料
        Tasks::truncate();
        TaskCategory::truncate();

        $nameArray = [
            'daily',
            'weekly',
            'monthly',
            'events',
            'special',
        ];

        $chNameArray = [
            'daily'   => "每日",
            'weekly'  => "每周",
            'monthly' => "每月",
            'events'  => "活動",
            'special' => "特殊",
        ];

        foreach ($nameArray as $name) {
            $showType = in_array($name, ['events', 'special'])
            ? [$name]                    // 不加 _bonus
            : [$name . '_bonus', $name]; // 有 _bonus

            TaskCategory::create([
                'name'                => $chNameArray[$name],
                'localization_name'   => $name === 'daily' ? 'quest_login' : 'quest_' . $name,
                'is_active'           => 1,
                'show_type'           => $showType,
                'show_page_prefab'    => in_array($name, ['events', 'special']) ? 'Quest_Ver2_Page_None' : 'Quest_Ver2_Page_Bonus',
                'bonus_task_start_id' => in_array($name, ['events', 'special']) ? -1 : 2001,
                'bonus_task_end_id'   => in_array($name, ['events', 'special']) ? -1 : ($name === 'weekly' ? 2007 : ($name === 'monthly' ? 2006 : 2005)),
            ]);
        }

        // 新增活動任務
        Tasks::create([
            'type'             => 'special',
            'description'      => '總登入天數達1天',
            'condition'        => ["action" => "login_total_days", "count" => 1],
            'reward'           => [
                ["item_id" => 101, "amount" => 15000],
            ],
            'start_at'         => now(),
            'end_at'           => null,
            'prev_task_id'     => null,
            'next_task_id'     => null,
            'is_auto_complete' => true,
            'repeatable_type'  => 0,
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 4,
            'auto_assign'      => true,
        ]);

        Tasks::create([
            'type'             => 'special',
            'description'      => '總登入天數達7天',
            'condition'        => ["action" => "login_total_days", "count" => 7],
            'reward'           => [
                ["item_id" => 101, "amount" => 15000],
            ],
            'start_at'         => now(),
            'end_at'           => null,
            'prev_task_id'     => null,
            'next_task_id'     => null,
            'is_auto_complete' => true,
            'repeatable_type'  => 0,
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 4,
            'auto_assign'      => true,
        ]);

        // 1. 每日登入任務
        $task1 = Tasks::create([
            'type'             => 'daily',
            'description'      => '每日登入獎勵',
            'condition'        => ["action" => "login", "count" => 1],
            'reward'           => [
                ["item_id" => 101, "amount" => 15000],
                ["item_id" => 102, "amount" => 100],
                ["item_id" => 2070005, "amount" => 1],
                ["item_id" => 2070006, "amount" => 1],
                ["item_id" => 2070007, "amount" => 1],
                ["item_id" => 2010000, "amount" => 1],
                ["item_id" => 2020004, "amount" => 1],
                ["item_id" => 2030000, "amount" => 1],
            ],
            'start_at'         => now(),
            'end_at'           => now()->addDays(30),
            'prev_task_id'     => null,
            'next_task_id'     => 2,
            'is_auto_complete' => true,
            'repeatable_type'  => 1, // 每日重置
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 1,
        ]);

        // 2. 連續登入7天任務
        $task2 = Tasks::create([
            'type'             => 'weekly',
            'description'      => '連續登入七天',
            'condition'        => ["action" => "login", "count" => 7],
            'reward'           => [
                ['item_id' => 101, 'amount' => 30000],
            ],
            'start_at'         => now(),
            'end_at'           => now()->addDays(60),
            'prev_task_id'     => 1,
            'next_task_id'     => 3,
            'is_auto_complete' => true,
            'repeatable_type'  => 2, // 每週重置
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 2,
        ]);

        // 3. 完成10場對戰
        $task3 = Tasks::create([
            'type'             => 'daily',
            'description'      => '完成10場對戰',
            'condition'        => ["action" => "battle", "count" => 10],
            'reward'           => [
                ['item_id' => 101, 'amount' => 20000],
            ],
            'start_at'         => now(),
            'end_at'           => now()->addDays(30),
            'prev_task_id'     => 2,
            'next_task_id'     => null,
            'is_auto_complete' => false,
            'repeatable_type'  => 1, // 每日重置
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 1,
        ]);

        // 4. 獲得5次勝利
        $task4 = Tasks::create([
            'type'             => 'daily',
            'description'      => '獲得5次勝利',
            'condition'        => ["action" => "win", "count" => 5],
            'reward'           => [
                ['item_id' => 101, 'amount' => 25000],
            ],
            'start_at'         => now(),
            'end_at'           => now()->addDays(30),
            'prev_task_id'     => null,
            'next_task_id'     => 5,
            'is_auto_complete' => false,
            'repeatable_type'  => 1, // 每日重置
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 1,
        ]);

        // 5. 收集100個金幣
        $task5 = Tasks::create([
            'type'             => 'monthly',
            'description'      => '收集100個金幣',
            'condition'        => ["action" => "collect_coins", "count" => 100],
            'reward'           => [
                ['item_id' => 101, 'amount' => 50000],
            ],
            'start_at'         => now(),
            'end_at'           => now()->addDays(45),
            'prev_task_id'     => 4,
            'next_task_id'     => 6,
            'is_auto_complete' => false,
            'repeatable_type'  => "-1", // 做完馬上重置
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 3,
        ]);

        // 6. 分享遊戲給朋友
        $task6 = Tasks::create([
            'type'             => 'events',
            'description'      => '分享遊戲給3位朋友',
            'condition'        => ["action" => "share", "count" => 3],
            'reward'           => [
                ['item_id' => 101, 'amount' => 60000],
            ],
            'start_at'         => now(),
            'end_at'           => now()->addDays(90),
            'prev_task_id'     => 5,
            'next_task_id'     => null,
            'is_auto_complete' => false,
            'repeatable_type'  => 0, // 不可重複
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 4,
        ]);

        // 7. 中秋節活動任務
        $task7 = Tasks::create([
            'type'             => 'events',
            'description'      => '中秋節收集50個月餅',
            'condition'        => ["action" => "collect_mooncakes", "count" => 50],
            'reward'           => [
                ['item_id' => 101, 'amount' => 88888],
            ],
            'start_at'         => Carbon::create(2025, 9, 10),
            'end_at'           => Carbon::create(2025, 9, 25),
            'prev_task_id'     => null,
            'next_task_id'     => null,
            'is_auto_complete' => false,
            'repeatable_type'  => 0, // 不可重複
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 4,
        ]);

        // 8. 新手引導任務
        $task8 = Tasks::create([
            'type'             => 'events',
            'description'      => '完成新手教學',
            'condition'        => ["action" => "complete_tutorial", "count" => 1],
            'reward'           => [
                ['item_id' => 101, 'amount' => 30000],
            ],
            'start_at'         => now(),
            'end_at'           => null, // 永久任務
            'prev_task_id'     => null,
            'next_task_id'     => 9,
            'is_auto_complete' => true,
            'repeatable_type'  => 0, // 不可重複
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 4,
        ]);

        // 9. 完成第一次對戰
        $task9 = Tasks::create([
            'type'             => 'events',
            'description'      => '完成第一次對戰',
            'condition'        => ["action" => "first_battle", "count" => 1],
            'reward'           => [
                ['item_id' => 101, 'amount' => 20000],
            ],
            'start_at'         => now(),
            'end_at'           => null, // 永久任務
            'prev_task_id'     => 8,
            'next_task_id'     => 10,
            'is_auto_complete' => true,
            'repeatable_type'  => "-1", // 做完馬上重置
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 4,
        ]);

        // 10. 購買商店物品
        $task10 = Tasks::create([
            'type'             => 'events',
            'description'      => '從商店購買任意物品',
            'condition'        => ["action" => "purchase", "count" => 1],
            'reward'           => [
                ['item_id' => 101, 'amount' => 25000],
            ],
            'start_at'         => now(),
            'end_at'           => now()->addDays(60),
            'prev_task_id'     => 9,
            'next_task_id'     => null,
            'is_auto_complete' => false,
            'repeatable_type'  => 0, // 不可重複
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 4,
        ]);

        // 11. 每月任務 - 累積登入15天
        $task11 = Tasks::create([
            'type'             => 'monthly',
            'description'      => '每月累積登入15天',
            'condition'        => ["action" => "login", "count" => 15],
            'reward'           => [
                ['item_id' => 101, 'amount' => 100000],
                ['item_id' => 102, 'amount' => 500],
            ],
            'start_at'         => null,
            'end_at'           => null, // 永久任務
            'prev_task_id'     => null,
            'next_task_id'     => null,
            'is_auto_complete' => true,
            'repeatable_type'  => 3, // 每月重置
            'is_active'        => true,
            'start_trigger'    => null,
            'end_trigger'      => null,
            'category_id'      => 3,
        ]);

        // 新增其他任務
        DB::table('tasks')->insert([
            [
                'id'                => 2001,
                'localization_name' => null,
                'description'       => '積分禮包20',
                'summary'           => null,
                'condition'         => '{"action": "bonus", "count": 20}',
                'check_id'          => null,
                'reward'            => '[{"item_id": 101, "amount": 30000}]',
                'start_at'          => now(),
                'end_at'            => now()->addDays(30),
                'prev_task_id'      => null,
                'next_task_id'      => 2002,
                'is_auto_complete'  => true,
                'is_active'         => true,
                'repeatable_type'   => '2',
                'start_trigger'     => null,
                'end_trigger'       => null,
                'type'              => 'daily_bonus',
                'category_id'       => 1,
            ],
            [
                'id'                => 2002,
                'localization_name' => null,
                'description'       => '積分禮包40',
                'summary'           => null,
                'condition'         => '{"action": "bonus", "count": 40}',
                'check_id'          => null,
                'reward'            => '[{"item_id": 101, "amount": 30000}]',
                'start_at'          => now(),
                'end_at'            => now()->addDays(30),
                'prev_task_id'      => 2001,
                'next_task_id'      => 2003,
                'is_auto_complete'  => true,
                'is_active'         => true,
                'repeatable_type'   => '2',
                'start_trigger'     => null,
                'end_trigger'       => null,
                'type'              => 'daily_bonus',
                'category_id'       => 1,
            ],
            [
                'id'                => 2003,
                'localization_name' => null,
                'description'       => '積分禮包60',
                'summary'           => null,
                'condition'         => '{"action": "bonus", "count": 60}',
                'check_id'          => null,
                'reward'            => '[{"item_id": 101, "amount": 30000}]',
                'start_at'          => now(),
                'end_at'            => now()->addDays(30),
                'prev_task_id'      => 2002,
                'next_task_id'      => 2004,
                'is_auto_complete'  => true,
                'is_active'         => true,
                'repeatable_type'   => '2',
                'start_trigger'     => null,
                'end_trigger'       => null,
                'type'              => 'daily_bonus',
                'category_id'       => 1,
            ],
            [
                'id'                => 2004,
                'localization_name' => null,
                'description'       => '積分禮包80',
                'summary'           => null,
                'condition'         => '{"action": "bonus", "count": 80}',
                'check_id'          => null,
                'reward'            => '[{"item_id": 101, "amount": 30000}]',
                'start_at'          => now(),
                'end_at'            => now()->addDays(30),
                'prev_task_id'      => 2003,
                'next_task_id'      => 2005,
                'is_auto_complete'  => true,
                'is_active'         => true,
                'repeatable_type'   => '2',
                'start_trigger'     => null,
                'end_trigger'       => null,
                'type'              => 'daily_bonus',
                'category_id'       => 1,
            ],
            [
                'id'                => 2005,
                'localization_name' => null,
                'description'       => '積分禮包85',
                'summary'           => null,
                'condition'         => '{"action": "bonus", "count": 85}',
                'check_id'          => null,
                'reward'            => '[{"item_id": 101, "amount": 30000}]',
                'start_at'          => now(),
                'end_at'            => now()->addDays(30),
                'prev_task_id'      => 2004,
                'next_task_id'      => 2006,
                'is_auto_complete'  => true,
                'is_active'         => true,
                'repeatable_type'   => '2',
                'start_trigger'     => null,
                'end_trigger'       => null,
                'type'              => 'daily_bonus',
                'category_id'       => 1,
            ],
            [
                'id'                => 2006,
                'localization_name' => null,
                'description'       => '積分禮包90',
                'summary'           => null,
                'condition'         => '{"action": "bonus", "count": 90}',
                'check_id'          => null,
                'reward'            => '[{"item_id": 101, "amount": 30000}]',
                'start_at'          => now(),
                'end_at'            => now()->addDays(30),
                'prev_task_id'      => 2005,
                'next_task_id'      => 2007,
                'is_auto_complete'  => true,
                'is_active'         => true,
                'repeatable_type'   => '2',
                'start_trigger'     => null,
                'end_trigger'       => null,
                'type'              => 'daily_bonus',
                'category_id'       => 1,
            ],
            [
                'id'                => 2007,
                'localization_name' => null,
                'description'       => '積分禮包100',
                'summary'           => null,
                'condition'         => '{"action": "bonus", "count": 100}',
                'check_id'          => null,
                'reward'            => '[{"item_id": 101, "amount": 30000}]',
                'start_at'          => now(),
                'end_at'            => now()->addDays(30),
                'prev_task_id'      => 2006,
                'next_task_id'      => null,
                'is_auto_complete'  => true,
                'is_active'         => true,
                'repeatable_type'   => '2',
                'start_trigger'     => null,
                'end_trigger'       => null,
                'type'              => 'daily_bonus',
                'category_id'       => 1,
            ],
        ]);

    }
}
