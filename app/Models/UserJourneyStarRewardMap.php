<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserJourneyStarRewardMap extends Model
{
    protected $table = 'user_journey_star_reward_maps';

    protected $fillable = [
        'uid',
        'reward_unique_id',
        'is_received',
    ];

    protected $casts = [
        'is_received' => 'integer',
    ];
}
