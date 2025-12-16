<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserItems extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'user_items';
    // protected $primaryKey = 'id';
    // use SoftDeletes;

    public const REGION_AVATAR = 'Avatar';

    public const REGION_MAP = 'Map';

    protected $hidden = [];

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    // protected $fillable = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'publish_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [];

    protected $_virtual = [];

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

    public function item()
    {
        return $this->belongsTo('App\Models\GddbItems', 'item_id', 'item_id');
    }

    /** 取得特定道具item_id+amount */
    public static function getUserFormattedItems(int $uid, array $itemIds): array
    {
        $itemIds = array_values(array_unique($itemIds));
        if (empty($itemIds)) {
            return [];
        }

        $items = self::where('uid', $uid)
            ->whereIn('item_id', $itemIds)
            ->get(['item_id', 'qty']);

        $formatted = [];
        foreach ($items as $item) {
            $formatted[] = [
                'item_id' => $item->item_id,
                'amount' => (int) $item->qty,
            ];
        }

        return $formatted;
    }
}
