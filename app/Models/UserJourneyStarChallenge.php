<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserJourneyStarChallenge extends Model
{
    protected $table = 'user_journey_star_challenges';

    protected $fillable = [
        'uid',
        'challenge_id',
        'stars_mask',
    ];

    protected $casts = [
        'stars_mask' => 'integer',
    ];

    /**
     * 取得關聯的使用者
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'id');
    }

    /**
     * 取得關聯的章節
     */
    public function journey()
    {
        return $this->belongsTo(GddbSurgameJourney::class, 'challenge_id', 'unique_id');
    }

    /**
     * 取得玩家的章節記錄
     */
    public function journeyRecord()
    {
        return $this->belongsTo(UserJourneyRecord::class, 'uid', 'uid');
    }
}
