<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthCardConfig extends Model
{
    protected $table = 'month_card_configs';

    protected $fillable = [
        'key',
        'localization_name',
        'basic_reward',
        'daily_reward',
        'add_days',
        'display_remaining',
        'max_purchase_times',
        'reset_buy_count',
        'enable_3x_speed',
        'unlimited_quick_patrol',
        'enable_patrol_reward',
        'patrol_reward_percent',
        'stage_reward_percent',
        'stamina_max',
        'black_market_extra_refresh_times',
        'is_permanent',
        'is_active',
        'desc'
    ];

    protected $casts = [
        'basic_reward' => 'array',
        'daily_reward' => 'array',
        'display_remaining' => 'boolean',
        'reset_buy_count' => 'boolean',
        'enable_3x_speed' => 'boolean',
        'unlimited_quick_patrol' => 'boolean',
        'enable_patrol_reward' => 'boolean',
        'is_permanent' => 'boolean',
        'is_active' => 'boolean',
        'desc' => 'array',
    ];

    /**
     * 取得擁有此月卡的所有用戶月卡記錄
     */
    public function userMonthCards(): HasMany
    {
        return $this->hasMany(UserMonthCard::class, 'month_card_config_id');
    }

    /**
     * 根據 key 取得月卡設定
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    /**
     * 取得所有啟用的月卡設定
     */
    public static function getActiveConfigs()
    {
        return static::where('is_active', true)->get();
    }
}
