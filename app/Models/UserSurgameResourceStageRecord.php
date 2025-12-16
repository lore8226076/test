<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSurgameResourceStageRecord extends Model
{
    protected $table = 'user_surgame_resource_stage_records';

    protected $fillable = [
        'uid',
        'stage_unique_id',
        'cleared_at',
        'type',
    ];

    protected $casts = [
        'uid' => 'integer',
        'stage_unique_id' => 'integer',
        'cleared_at' => 'timestamp',
        'type' => 'string',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;

    public static function markCleared(int $uid, int $stageUniqueId, string $type, $at = null): self
    {
        return self::firstOrCreate(
            [
                'uid' => $uid,
                'stage_unique_id' => $stageUniqueId,
                'type' => $type,
            ],
            [
                'cleared_at' => $at ?? now(),
            ]
        );
    }

    public function scopeForUser($query, int $uid)
    {
        return $query->where('uid', $uid);
    }
}
