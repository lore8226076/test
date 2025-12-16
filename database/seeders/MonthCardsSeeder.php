<?php

namespace Database\Seeders;

use App\Models\MonthCardConfig;
use Illuminate\Database\Seeder;

class MonthCardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $monthCards = [
            [
                'id' => 1,
                'localization_name' => '靈感月卡',
                'basic_reward' => [100, 300],
                'daily_reward' => [100, 100],
                'add_days' => 30,
                'display_remaining' => true,
                'max_purchase_times' => 12,
                'reset_buy_count' => true,
                'enable_3x_speed' => true,
                'unlimited_quick_patrol' => true,
                'enable_patrol_reward' => true,
                'patrol_reward_percent' => 10,
                'stage_reward_percent' => 0,
                'stamina_max' => 20,
                'black_market_extra_refresh_times' => 0,
                'key' => 'month001',
                'is_permanent' => false,
                'is_active' => true,
                'desc' => [
                    'top-up.benefit.spd3x',
                    'top-up.benefit.patrolreward',
                    'top-up.benefit.maxap',
                ],
            ],
            [
                'id' => 2,
                'localization_name' => '繪夢月卡',
                'basic_reward' => [100, 450],
                'daily_reward' => [100, 150],
                'add_days' => 30,
                'display_remaining' => true,
                'max_purchase_times' => 12,
                'reset_buy_count' => true,
                'enable_3x_speed' => false,
                'unlimited_quick_patrol' => true,
                'enable_patrol_reward' => true,
                'patrol_reward_percent' => 20,
                'stage_reward_percent' => 0,
                'stamina_max' => 0,
                'black_market_extra_refresh_times' => 2,
                'key' => 'spmonth001',
                'is_permanent' => false,
                'is_active' => true,
                'desc' => [
                    'top-up.benefit.unlimitedquickpatrol',
                    'top-up.benefit.secretrefresh',
                    'top-up.benefit.patrolreward',
                ],
            ],
            [
                'id' => 3,
                'localization_name' => '彩耀永恆卡',
                'basic_reward' => [100, 2300],
                'daily_reward' => [100, 1500],
                'add_days' => 9999999,
                'display_remaining' => false,
                'max_purchase_times' => 1,
                'reset_buy_count' => false,
                'enable_3x_speed' => false,
                'unlimited_quick_patrol' => false,
                'enable_patrol_reward' => false,
                'patrol_reward_percent' => 0,
                'stage_reward_percent' => 50,
                'stamina_max' => 0,
                'black_market_extra_refresh_times' => 0,
                'key' => 'forever001',
                'is_permanent' => true,
                'is_active' => true,
                'desc' => [
                    'top-up.benefit.stagebonus',
                ],
            ],
        ];

        foreach ($monthCards as $card) {
            if (! MonthCardConfig::where('id', $card['id'])->exists()) {
                MonthCardConfig::create($card);
            }else {
                echo "MonthCardConfig ID {$card['id']} 已存在，跳過建立。\n";
            }
        }
    }
}
