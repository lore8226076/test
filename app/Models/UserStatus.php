<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStatus extends Model
{
    use HasFactory;

    protected $table = 'user_statuses';

    protected $fillable = [
        'uid',
        'stamina',
        'stamina_max',
        'next_recover_at',
        'sweep_count',
        'sweep_max',
        'patrol_count',
        'patrol_max',
    ];

    protected $casts = [
        'next_recover_at' => 'datetime',
    ];

    // 減少玩家巡邏次數
    public static function decreasePatrolCount(int $uid, int $amount = 1)
    {
        try {
            $userStatus = self::where('uid', $uid)->first();
            if ($userStatus && $userStatus->patrol_count >= $amount) {
                $userStatus->patrol_count -= $amount;
                $userStatus->save();
            }
        } catch (\Exception $e) {
            \Log::error("扣除玩家巡邏次數失敗，UID: {$uid}, 錯誤訊息: " . $e->getMessage());
            return false;
        }

        return true;
    }
}
