<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserJourneyRewardMap extends Model
{
    protected $table = 'user_journey_reward_maps';

    protected $fillable = [
        'uid',
        'reward_id',
        'is_received',
    ];

    protected $casts = [
        'is_received' => 'integer',
    ];
}
