<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Users;

class UserReport extends Model
{
    protected $table = 'user_reports';

    protected $fillable = [
        'reporter_uid',
        'reported_uid',
        'type',
        'reason',
        'status',
        'reported_at',
    ];

    // 舉報者
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'reporter_uid', 'uid');
    }

    // 被舉報者
    public function reported(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'reported_uid', 'uid');
    }
}
