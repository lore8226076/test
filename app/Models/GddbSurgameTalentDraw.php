<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GddbSurgameTalentDraw extends Model
{
    protected $table    = 'gddb_surgame_talent_draw';
    public $timestamps  = false;
    protected $fillable = [
        'account_lv',
        'cost',
        'amount',
        'card_pool',
    ];

    protected $casts = [
        'card_pool' => 'array',
    ];

    /**
     * 對應的用戶獎池紀錄
     */
    public function userTalentPools()
    {
        return $this->hasMany(UserTalentPoolSession::class, 'talent_draw_id', 'id');
    }
}
