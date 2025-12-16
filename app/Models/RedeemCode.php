<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RedeemCode extends Model
{
    use SoftDeletes;

    protected $table = 'redeem_codes';

    protected $fillable = [
        'code', 'name', 'start_at', 'end_at',
        'rewards', 'memo',
    ];

    protected $dates = ['start_at', 'end_at', 'deleted_at'];

    protected $casts = [
        'rewards' => 'array',
    ];

    // 和 user mapping 關聯
    public function userRedeemCodes()
    {
        return $this->hasMany(UserRedeemCode::class, 'redeem_code_id');
    }

    // 取得已兌換的所有 user
    public function users()
    {
        return $this->belongsToMany(Users::class, 'user_redeem_codes', 'redeem_code_id', 'uid');
    }

    // 檢查兌換碼是否過期
    public function isExpired()
    {
        return $this->end_date && $this->end_date < now();
    }
}
