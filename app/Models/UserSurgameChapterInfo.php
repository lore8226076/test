<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSurgameChapterInfo extends Model
{
    protected $table = 'user_surgame_chapter_records';

    protected $fillable = [
        'uid',
        'current_chapter',
        'current_wave',
        'total_stars',
    ];

    protected $casts = [
        'current_chapter' => 'integer',
        'current_wave'    => 'integer',
        'total_stars'     => 'integer',
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
    public function currentChapter()
    {
        return $this->belongsTo(GddbSurgameJourney::class, 'current_chapter', 'unique_id');
    }

    /**
     * 取得玩家的星級挑戰進度
     */
    public function starChallenges()
    {
        return $this->hasMany(UserSurgameStarChallengeProgress::class, 'uid', 'uid');
    }
}
