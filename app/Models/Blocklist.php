<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Users;

class Blocklist extends Model
{
    protected $table = 'blocklists';

    protected $fillable = [
        'uid',
        'blocked_uid',
        'blocked_at',
    ];

    protected $casts = [
        'blocked_at' => 'timestamp',
    ];

    public $timestamps = false;

    public static function isBlocked(string $uid, string $targetUid): bool
    {
        return self::where('uid', $uid)
            ->where('blocked_uid', $targetUid)
            ->exists();
    }

    // 封鎖者
    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }

    // 被封鎖者
    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'blocked_uid', 'uid');
    }
}
