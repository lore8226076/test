<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserJourneyRecord extends Model
{
    protected $table = 'user_journey_records';

    protected $fillable = [
        'uid',
        'current_journey_id',
        'current_wave',
        'total_stars'
    ];

    /**
     * 取得關聯的使用者
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'id');
    }

    /**
     * 取得關聯的當前章節
     */
    public function currentJourney()
    {
        return $this->belongsTo(GddbSurgameJourney::class, 'current_journey_id', 'unique_id');
    }

    /**
     * 取得玩家的星級挑戰記錄
     */
    public function starChallenges()
    {
        return $this->hasMany(UserJourneyStarChallenge::class, 'uid', 'uid');
    }

}
