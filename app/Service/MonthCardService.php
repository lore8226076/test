<?php

namespace App\Service;

use App\Models\MonthCardConfig;
use App\Models\UserMonthCard;
use App\Models\Users;

class MonthCardService extends AppService
{
    /**
     * 取得用戶所有有效月卡的加成效果
     */
    public static function getUserMonthCardBonuses(int $uid): array
    {
        $userMonthCards = UserMonthCard::with('config')
            ->where('uid', $uid)
            ->active()
            ->get();

        $bonuses = [
            'enable_3x_speed' => false,
            'unlimited_quick_patrol' => false,
            'enable_patrol_reward' => false,
            'patrol_reward_percent' => 0,
            'stage_reward_percent' => 0,
            'stamina_max' => 0,
            'black_market_extra_refresh_times' => 0,
        ];

        foreach ($userMonthCards as $userMonthCard) {
            if (! $userMonthCard->isValid()) {
                continue;
            }

            $config = $userMonthCard->config;
            if (! $config) {
                continue;
            }

            // 布林值使用 OR 邏輯
            $bonuses['enable_3x_speed'] = $bonuses['enable_3x_speed'] || $config->enable_3x_speed;
            $bonuses['unlimited_quick_patrol'] = $bonuses['unlimited_quick_patrol'] || $config->unlimited_quick_patrol;
            $bonuses['enable_patrol_reward'] = $bonuses['enable_patrol_reward'] || $config->enable_patrol_reward;

            // 數值使用累加
            $bonuses['patrol_reward_percent'] += $config->patrol_reward_percent;
            $bonuses['stage_reward_percent'] += $config->stage_reward_percent;
            $bonuses['stamina_max'] += $config->stamina_max;
            $bonuses['black_market_extra_refresh_times'] += $config->black_market_extra_refresh_times;
        }

        return $bonuses;
    }

    /**
     * 取得用戶的關卡獎勵加成百分比（取代原本的 resource_stage_bonus_rate）
     */
    public static function getStageRewardPercent(int $uid): int
    {
        $bonuses = self::getUserMonthCardBonuses($uid);

        return $bonuses['stage_reward_percent'];
    }

    /**
     * 取得用戶的巡邏獎勵加成百分比
     */
    public static function getPatrolRewardPercent(int $uid): int
    {
        $bonuses = self::getUserMonthCardBonuses($uid);

        return $bonuses['patrol_reward_percent'];
    }

    /**
     * 取得用戶的額外體力上限
     */
    public static function getExtraStaminaMax(int $uid): int
    {
        $bonuses = self::getUserMonthCardBonuses($uid);
        return $bonuses['stamina_max'];
    }

    /**
     * 檢查用戶是否擁有有效的指定月卡
     */
    public static function hasValidMonthCard(int $uid, string $configKey): bool
    {
        $config = MonthCardConfig::findByKey($configKey);
        if (! $config) {
            return false;
        }

        $userMonthCard = UserMonthCard::where('uid', $uid)
            ->where('month_card_config_id', $config->id)
            ->first();

        return $userMonthCard && $userMonthCard->isValid();
    }

    /**
     * 檢查用戶是否擁有任何有效月卡
     */
    public static function hasAnyValidMonthCard(int $uid): bool
    {
        return UserMonthCard::where('uid', $uid)->active()->exists();
    }

    /**
     * 購買月卡
     */
    public static function purchaseMonthCard(Users $user, MonthCardConfig $config): array
    {
        $userMonthCard = UserMonthCard::findOrCreateForUser(
            $user->id,
            $user->uid,
            $config->id
        );

        // 檢查購買次數限制
        if ($config->max_purchase_times > 0 && $userMonthCard->total_purchase_times >= $config->max_purchase_times) {
            return [
                'success' => false,
                'error_code' => 'MonthCard:0001', // 已達購買上限
            ];
        }

        // 是否首次購買（用於發放首購獎勵）
        $isFirstPurchase = $userMonthCard->total_purchase_times === 0;

        // 套用購買
        $userMonthCard->applyPurchaseFromConfig($config);
        $userMonthCard->save();

        return [
            'success' => true,
            'is_first_purchase' => $isFirstPurchase,
            'basic_reward' => $isFirstPurchase ? $config->basic_reward : null,
            'expire_at' => $userMonthCard->expire_at,
            'total_purchase_times' => $userMonthCard->total_purchase_times,
        ];
    }

    /**
     * 領取每日獎勵
     */
    public static function claimDailyReward(int $uid, int $configId): array
    {
        $userMonthCard = UserMonthCard::with('config')
            ->where('uid', $uid)
            ->where('month_card_config_id', $configId)
            ->first();

        if (! $userMonthCard) {
            return [
                'success' => false,
                'error_code' => 'MonthCard:0002', // 未擁有此月卡
            ];
        }

        if (! $userMonthCard->isValid()) {
            return [
                'success' => false,
                'error_code' => 'MonthCard:0003', // 月卡已過期
            ];
        }

        if ($userMonthCard->hasClaimedDailyReward()) {
            return [
                'success' => false,
                'error_code' => 'MonthCard:0004', // 今日已領取
            ];
        }

        $userMonthCard->claimDailyReward();

        return [
            'success' => true,
            'daily_reward' => $userMonthCard->config->daily_reward,
        ];
    }

    /**
     * 取得用戶所有月卡狀態
     */
    public static function getUserMonthCardStatus(int $uid): array
    {
        $configs = MonthCardConfig::getActiveConfigs();
        $userMonthCards = UserMonthCard::where('uid', $uid)->get()->keyBy('month_card_config_id');

        $result = [];

        foreach ($configs as $config) {
            $userCard = $userMonthCards->get($config->id);

            $result[] = [
                // 基本資訊
                'unique_id' => $config->id,
                'key' => $config->key,
                'name' => $config->localization_name,

                // 獎勵設定
                'basic_reward' => self::formatRewards($config->basic_reward ?? []),
                'daily_reward' => self::formatRewards($config->daily_reward ?? []),

                // 月卡設定
                'add_days' => $config->add_days,
                'is_permanent' => $config->is_permanent ? 1 : -1,
                'display_remaining' => $config->display_remaining ? 1 : -1,
                'max_purchase_times' => $config->max_purchase_times,
                'reset_buy_count' => $config->reset_buy_count ? 1 : -1,

                // 加成效果
                'enable_3x_speed' => $config->enable_3x_speed ? 1 : -1,
                'unlimited_quick_patrol' => $config->unlimited_quick_patrol ? 1 : -1,
                'enable_patrol_reward' => $config->enable_patrol_reward ? 1 : -1,
                'patrol_reward_percent' => $config->patrol_reward_percent,
                'stage_reward_percent' => $config->stage_reward_percent,
                'stamina_max' => $config->stamina_max,
                'black_market_extra_refresh_times' => $config->black_market_extra_refresh_times,

                // 加成描述
                'desc' => $config->desc ?? [],

                // 用戶狀態
                'is_owned' => $userCard !== null ? 1 : -1,
                'is_valid' => $userCard && $userCard->isValid() ? 1 : -1,
                'expire_at' => $userCard?->expire_at?->timestamp,
                'remaining_days' => $userCard?->getRemainingDays(),
                'can_claim_daily' => $userCard && $userCard->canClaimDailyReward() ? 1 : -1,
                'total_purchase_times' => $userCard?->total_purchase_times ?? 0,
            ];
        }

        return $result;
    }

    /**
     * 獎勵轉換
     * [100, 300] => [['item_id' => 100, 'amount' => 300]]
     * [[100, 200], [101, 5]] => [['item_id' => 100, 'amount' => 200], ['item_id' => 101, 'amount' => 5]]
     */
    public static function formatRewards(array $rewards): array
    {
        $formatted = [];

        // 檢查是否為單一獎勵格式 [item_id, amount]
        if (count($rewards) === 2 && is_numeric($rewards[0]) && is_numeric($rewards[1])) {
            return [[
                'item_id' => $rewards[0],
                'amount' => $rewards[1],
            ]];
        }

        // 多獎勵格式 [[item_id, amount], [item_id, amount], ...]
        foreach ($rewards as $reward) {
            if (is_array($reward) && count($reward) >= 2) {
                $formatted[] = [
                    'item_id' => $reward[0],
                    'amount' => $reward[1],
                ];
            }
        }

        return $formatted;
    }

    /**
     * 無限掃蕩檢查
     */
    public static function hasUnlimitedQuickPatrol(int $uid): bool
    {
        $bonuses = self::getUserMonthCardBonuses($uid);
        return $bonuses['unlimited_quick_patrol'] ?? false;
    }
}
