<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MiniGameRanks extends BaseModel
{
    protected $table = 'mini_game_ranks';

    protected $fillable = [
        'game_id',
        'user_id',
        'score',
        'total_time',
    ];

    public static function getMiniGameTypes(){
        return [
            '1' => '射飛鏢',
            '2' => '翻卡牌',
            '3' => '下樓梯',
        ];
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Users', 'user_id');
    }
}
