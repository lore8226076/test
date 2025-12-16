<?php

namespace App\Models;

class UserItemLogs extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'user_item_logs';
    // protected $primaryKey = 'id';
    // use SoftDeletes;

    protected $hidden = [];

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    // protected $fillable = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = ['type_text'];

    protected $_virtual = ['type_text'];

    public const TYPE_INIT = 1;

    public const TYPE_SYSTEM = 2;

    public const TYPE_SHOP_BUY = 10;

    public const TYPE_ORDER_BUY = 11; // 儲值購買

    public const TYPE_GACHA = 12;

    public const TYPE_SHOP_GROUP_BUY = 13; // 商城群組道具

    public const TYPE_BUY_VOUCHER = 14; // 購買代金券

    public const TYPE_VOUCHER_BUY = 15; // 使用代金券購買

    public const TYPE_BUY_DIAMOND = 16; // 購買鑽石

    public const TYPE_ITEM_USE = 21;

    // 巡邏與快速巡邏 30,31
    public const TYPE_PATROL = 30;

    public const TYPE_QUICK_PATROL = 31;

    // 主線道具取得 32
    public const TYPE_MAIN_CHAPTER = 32;

    public const TYPE_FIRST_CLEAR = 33; // 首通獎勵

    public const TYPE_SWEEP_MONEY_RESOURCE = 34; // 金幣資源關卡掃蕩獎勵

    public const TYPE_SWEEP_EXP_RESOURCE = 35; // 經驗資源關卡掃蕩獎勵

    public const TYPE_SWEEP_GIFT_RESOURCE = 36; // 靈感資源關卡掃蕩獎勵

    // 取消訂單相關
    public const TYPE_SHOP_CANCEL = 40;

    public const TYPE_ORDER_CANCEL = 41;

    // 英雄相關
    public const TYPE_CHARACTER = 51; // 獲得英雄

    public const TYPE_CHARACTER_FRAGMENT = 52; // 角色碎片

    // 軍階相關
    public const TYPE_GRADE_TASK = 61;

    public const TYPE_GRADE_UPGRADE = 62;

    // 裝備相關
    public const TYPE_EQUIP_REWARD = 70; // 裝備獎勵

    public const TYPE_EQUIP_DISMANTLE = 71; // 裝備分解

    public const TYPE_EQUIP_ENHANCE = 72; // 裝備強化

    // 禮包相關
    public const TYPE_ITEM_PACKAGE = 80; // 禮包道具

    public const REGION_MAP = 'Map';

    // 天賦抽取
    public const TYPE_TALENT_DRAW = 90; // 天賦抽取

    public static function getTypes()
    {
        return [
            1 => __('初始發放'),
            2 => __('系統發放'),

            10 => __('商城購買'),
            11 => __('儲值購買'),
            12 => __('扭蛋抽取獲得'),
            13 => __('商城群組道具'),
            14 => __('購買代金券'),
            15 => __('使用代金券購買'),
            16 => __('購買鑽石'),

            21 => __('貨幣使用'),
            30 => __('巡邏獲得'),
            31 => __('快速巡邏獲得'),
            32 => __('主線道具取得'),
            33 => __('首通獎勵'),
            34 => __('金幣資源關卡掃蕩獎勵'),
            35 => __('經驗資源關卡掃蕩獎勵'),
            36 => __('靈感資源關卡掃蕩獎勵'),

            40 => __('商城取消購買'),
            41 => __('儲值取消購買'),
            51 => __('獲得英雄'),
            52 => __('角色碎片'),

            61 => __('軍階任務獎勵'),
            62 => __('軍階升級獎勵'),

            70 => __('裝備獎勵'),
            71 => __('裝備分解'),
            72 => __('裝備強化'),

            80 => __('禮包道具'),
        ];
    }

    public function getTypeTextAttribute()
    {
        return isset($this->getTypes()[$this->type]) ? $this->getTypes()[$this->type] : $this->type;
    }

    protected static function boot()
    {
        parent::boot();
        // creating, created, saving, saved, deleting, deleted
        static::creating(function ($entity) {});
        static::saving(function ($entity) {});
        static::saved(function ($entity) {});
        static::deleting(function ($entity) {});
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Users', 'user_id');
    }

    public function userItem()
    {
        return $this->belongsTo('App\Models\UserItems', 'user_item_id');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\GddbItems', 'item_id', 'id');
    }

    // 檢查是否為鑽石首購
    public static function isFirstPurchase($userId, $itemId, $afterQty)
    {
        $types = [self::TYPE_VOUCHER_BUY, self::TYPE_BUY_DIAMOND];
        $logCount = UserItemLogs::where([
            'user_id' => $userId,
            'item_id' => $itemId,
            'original_qty' => 0,
            'after_qty' => $afterQty,
        ])
            ->whereIn('type', $types)
            ->count();

        dump("User ID: {$userId}, Item ID: {$itemId}, After Qty: {$afterQty}, Log Count: {$logCount}, Types: " . implode(',', $types));

        return $logCount == 0;
    }

    public static function changeQty($type, $user_id, $user_item_id, $item_id, $manager_id, $original_qty, $qty, $memo, $user_mall_order_id = null, $user_pay_order_id = null, $user_gacha_order_id = null)
    {
        $userItemLog = new UserItemLogs;
        $userItemLog->type = $type;
        $userItemLog->user_id = $user_id;
        $userItemLog->user_item_id = $user_item_id;
        $userItemLog->item_id = $item_id;
        $userItemLog->manager_id = $manager_id;
        $userItemLog->original_qty = $original_qty;
        $userItemLog->qty = $qty;
        $userItemLog->after_qty = $userItemLog->original_qty + $userItemLog->qty;
        $userItemLog->memo = $memo;
        $userItemLog->user_mall_order_id = $user_mall_order_id;
        $userItemLog->user_pay_order_id = $user_pay_order_id;
        $userItemLog->user_gacha_order_id = $user_gacha_order_id;
        $userItemLog->save();
    }
}
