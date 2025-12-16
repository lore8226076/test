<?php
namespace App\Models;

use App\Models\InboxAttachments;
use App\Models\InboxTargets;
use App\Models\UserInboxEntries;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxMessages extends Model
{
    use HasFactory;

    protected $table = 'inbox_messages';

    protected $fillable = [
        'sender_type',
        'target_type',
        'status',
        'title',
        'content',
        'expire_at',
        'start_at',
        'end_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // 時間改時間戳
    protected $casts = [
        'expire_at' => 'timestamp',
        'start_at'  => 'timestamp',
        'end_at'    => 'timestamp',
    ];

    public function entries()
    {
        return $this->hasMany(UserInboxEntries::class, 'inbox_messages_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(InboxAttachments::class, 'inbox_messages_id', 'id');
    }

    public function targets()
    {
        return $this->hasMany(InboxTargets::class, 'inbox_messages_id', 'id');
    }

}
