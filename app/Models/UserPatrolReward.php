<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users;

class UserPatrolReward extends Model
{
    use HasFactory;

    protected $table = 'user_patrol_rewards';

    protected $fillable = [
        'uid',
        'last_claimed_at',
        'pending_minutes',
    ];

    protected $casts = [
        'last_claimed_at' => 'timestamp',
        'pending_minutes' => 'integer',
    ];

    /**
     * 關聯到玩家(User)
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }
}
