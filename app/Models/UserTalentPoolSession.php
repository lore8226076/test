<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserTalentSessionLog;
class UserTalentPoolSession extends Model
{
    protected $table = 'user_talent_pool_sessions';

    protected $fillable = [
        'uid',
        'talent_draw_id',
        'level_at_bind',
        'current_remaining',
        'status'
    ];

    protected $casts = [
        'current_remaining' => 'array',
        'status' => 'string'
    ];

    /**
     * 取得該抽獎紀錄的所有抽獎日誌
     */
    public function logs()
    {
        return $this->hasMany(UserTalentSessionLog::class, 'session_id', 'id');
    }

      /**
     * 角色關聯紀錄
     */
    public function surgameInfo()
    {
        return $this->belognsTo(UserSurGameInfo::class, 'uid', 'uid');
    }

    /**
     * 對應獎池
     */
    public function talentDraw()
    {
        return $this->belongsTo(GddbSurgameTalentDraw::class, 'talent_draw_id', 'id');
    }
}
