<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgamePassiveReward extends Model
{
    use HasFactory;

    protected $table = 'gddb_surgame_passive_rewards';
    public $timestamps = false;
    protected $fillable = [
        'now_stage',
        'rand_reward',
        'hour_coin',
        'hour_exp',
        'hour_crystal',
        'hour_paint',
        'hour_xp',
    ];
}
