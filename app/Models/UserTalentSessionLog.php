<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserTalentPoolSession;

class UserTalentSessionLog extends Model
{
    protected $table = 'user_talent_session_logs';

    protected $fillable = [
        'uid',
        'session_id',
        'item_code'
    ];

    /**
     * 取得關聯的抽獎紀錄
     */
    public function session()
    {
        return $this->belongsTo(UserTalentPoolSession::class, 'session_id', 'id');
    }
}
