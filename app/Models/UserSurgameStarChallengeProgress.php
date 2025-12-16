<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSurgameStarChallengeProgress extends Model
{
    protected $table = 'user_surgame_star_challenge_progress';

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
    public function chapter()
    {
        return $this->belongsTo(GddbSurgameJourney::class, 'challenge_id', 'unique_id');
    }

    /**
     * 取得玩家的章節資訊
     */
    public function chapterInfo()
    {
        return $this->belongsTo(UserSurgameChapterInfo::class, 'uid', 'uid');
    }
}
