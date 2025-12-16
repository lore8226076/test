<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMonthCard extends Model
{
    protected $table = 'user_month_cards';

    protected $fillable = [
        'user_id',
        'uid',
        'month_card_config_id',
        'purchased_at',
        'expire_at',
        'last_daily_reward_at',
        'total_purchase_times',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expire_at' => 'datetime',
        'last_daily_reward_at' => 'date',
        'total_purchase_times' => 'integer',
    ];

    /**
     * 關聯用戶
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    /**
     * 關聯月卡設定
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(MonthCardConfig::class, 'month_card_config_id');
    }

    /**
     * 檢查月卡是否有效
     */
    public function isValid(?DateTimeInterface $now = null): bool
    {
        $now = $now ?: now();

        // 永久卡永遠有效
        if ($this->config && $this->config->is_permanent) {
            return true;
        }

        if ($this->expire_at === null) {
            return false;
        }

        return $this->expire_at->greaterThan($now);
    }

    /**
     * 檢查今天是否已領取每日獎勵
     */
    public function hasClaimedDailyReward(?DateTimeInterface $now = null): bool
    {
        $now = $now ?: now();

        if ($this->last_daily_reward_at === null) {
            return false;
        }

        return $this->last_daily_reward_at->isSameDay($now);
    }

    /**
     * 檢查是否可以領取每日獎勵
     */
    public function canClaimDailyReward(?DateTimeInterface $now = null): bool
    {
        return $this->isValid($now) && ! $this->hasClaimedDailyReward($now);
    }

    /**
     * 領取每日獎勵
     */
    public function claimDailyReward(?DateTimeInterface $now = null): bool
    {
        $now = $now ?: now();

        if (! $this->canClaimDailyReward($now)) {
            return false;
        }

        $this->last_daily_reward_at = $now;
        $this->save();

        return true;
    }

    /**
     * 購買或延長月卡
     */
    public function applyPurchaseFromConfig(MonthCardConfig $config, ?DateTimeInterface $now = null): void
    {
        $now = $now ?: now();

        $this->month_card_config_id = $config->id;
        $this->purchased_at = $now;

        if ($config->is_permanent) {
            // 永久卡不設定到期時間
            $this->expire_at = null;
        } else {
            if ($this->expire_at && $this->expire_at->greaterThan($now)) {
                // 尚未過期，從當前到期時間延長到第 N 天的 23:59:59
                $this->expire_at = $this->expire_at->copy()->addDays($config->add_days)->endOfDay();
            } else {
                // 已過期或新購買，從今天開始計算到第 N 天的 23:59:59
                $this->expire_at = $now->copy()->addDays($config->add_days)->endOfDay();
            }
        }

        $this->total_purchase_times = ($this->total_purchase_times ?? 0) + 1;
    }

    /**
     * 取得剩餘天數
     */
    public function getRemainingDays(?DateTimeInterface $now = null): ?int
    {
        $now = $now ?: now();

        // 永久卡回傳 null 或特殊值
        if ($this->config && $this->config->is_permanent) {
            return null;
        }

        if ($this->expire_at === null) {
            return 0;
        }

        $remaining = $now->diffInDays($this->expire_at, false);

        return max(0, (int) ceil($remaining));
    }

    /**
     * Scope: 查詢有效的月卡
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereHas('config', function ($configQuery) {
                $configQuery->where('is_permanent', true);
            })
            ->orWhere('expire_at', '>', now());
        });
    }

    /**
     * Scope: 查詢已過期的月卡
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereHas('config', function ($configQuery) {
            $configQuery->where('is_permanent', false);
        })->where('expire_at', '<=', now());
    }

    /**
     * 根據用戶和月卡設定取得或建立記錄
     */
    public static function findOrCreateForUser(int $userId, int $uid, int $configId): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'month_card_config_id' => $configId,
            ],
            [
                'uid' => $uid,
                'purchased_at' => now(),
                'total_purchase_times' => 0,
            ]
        );
    }
}
