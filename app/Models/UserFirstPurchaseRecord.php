<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFirstPurchaseRecord extends Model
{
    protected $table = 'user_first_purchase_records';

    protected $fillable = [
        'user_id',
        'uid',
        'product_id',
        'purchase_type',
        'item_id',
        'month_card_config_id',
        'is_first_purchase',
        'reward_sent',
        'first_purchase_at',
        'reward_sent_at',
        'reward_detail',
    ];

    protected $casts = [
        'is_first_purchase' => 'boolean',
        'reward_sent' => 'boolean',
        'first_purchase_at' => 'datetime',
        'reward_sent_at' => 'datetime',
        'reward_detail' => 'array',
    ];

    // 購買類型常數
    public const TYPE_ITEM = 'item';
    public const TYPE_MONTH_CARD = 'month_card';

    /**
     * 關聯用戶
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    /**
     * 關聯月卡配置
     */
    public function monthCardConfig(): BelongsTo
    {
        return $this->belongsTo(MonthCardConfig::class, 'month_card_config_id');
    }

    /**
     * 檢查是否為首購
     */
    public static function isFirstPurchase(int $userId, string $productId): bool
    {
        return !static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * 記錄首購（道具類型）
     */
    public static function recordItemPurchase(
        int $userId,
        int $uid,
        string $productId,
        int $itemId,
        bool $isFirstPurchase
    ): self {
        return static::create([
            'user_id' => $userId,
            'uid' => $uid,
            'product_id' => $productId,
            'purchase_type' => self::TYPE_ITEM,
            'item_id' => $itemId,
            'is_first_purchase' => $isFirstPurchase,
            'first_purchase_at' => now(),
        ]);
    }

    /**
     * 記錄首購（月卡類型）
     */
    public static function recordMonthCardPurchase(
        int $userId,
        int $uid,
        string $productId,
        int $monthCardConfigId,
        bool $isFirstPurchase
    ): self {
        return static::create([
            'user_id' => $userId,
            'uid' => $uid,
            'product_id' => $productId,
            'purchase_type' => self::TYPE_MONTH_CARD,
            'month_card_config_id' => $monthCardConfigId,
            'is_first_purchase' => $isFirstPurchase,
            'first_purchase_at' => now(),
        ]);
    }

    /**
     * 標記獎勵已發放
     */
    public function markRewardSent(array $rewardDetail = []): void
    {
        $this->update([
            'reward_sent' => true,
            'reward_sent_at' => now(),
            'reward_detail' => $rewardDetail,
        ]);
    }

    /**
     * 取得用戶的所有首購記錄
     */
    public static function getUserFirstPurchases(int $uid): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('uid', $uid)
            ->where('is_first_purchase', true)
            ->orderBy('first_purchase_at', 'desc')
            ->get();
    }

    /**
     * 取得用戶特定商品的首購記錄
     */
    public static function getUserProductRecord(int $userId, string $productId): ?self
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }
}
