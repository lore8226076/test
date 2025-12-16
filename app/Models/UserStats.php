<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStats extends Model
{
    use HasFactory;

    protected $table = 'user_stats';

    protected $fillable = [
        'uid',
        'login_streak_days',
        'login_streak_max',
        'recharge_peak_amount',
        'recharge_recent_amount',
        'recharge_total_amount',
        'gacha_draw_times',
        'gacha_draw_total',
        'map_edit_times',
        'map_edit_total',
        'ugc_play_times',
        'ugc_play_total',
        'ugc_clear_times',
        'ugc_clear_total',
        'spend_stamina_total',
        'sr_accessory_obtained_count',
        'sr_accessory_owned_count',
        'ssr_accessory_obtained_count',
        'ssr_accessory_owned_count',
        'summon_count1',
        'summon_count2',
        'summon_count3',
        'summon_times_pig',
        'summon_times_chameleon',
        'summon_times_cow',
        'summon_times_rabbit',
        'summon_times_pufferfish',
        'summon_times_bear',
        'summon_count3_samepet',
        'draw_hero_times',
    ];
}
