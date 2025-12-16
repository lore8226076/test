<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRedeemCode extends Model
{

    protected $table = 'user_redeem_codes';

    protected $fillable = [
        'uid', 'redeem_code_id', 'redeemed_at', 'reward_snapshot'
    ];

    protected $dates = ['redeemed_at', 'deleted_at'];

    protected $casts = [
        'reward_snapshot' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid');
    }

    public function redeemCode()
    {
        return $this->belongsTo(RedeemCode::class, 'redeem_code_id');
    }
}
