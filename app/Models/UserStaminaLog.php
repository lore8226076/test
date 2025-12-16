<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users;

class UserStaminaLog extends Model
{
    use HasFactory;

    protected $table = 'user_stamina_logs';

    protected $fillable = [
        'uid',
        'change_stamina',
        'before_stamina',
        'after_stamina',
        'stage_id',
        'remark',
        'type',
        'next_recover_at',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }
}
