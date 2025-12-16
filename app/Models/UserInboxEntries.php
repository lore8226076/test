<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InboxMessages;
use App\Models\Users;

class UserInboxEntries extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_inbox_entries';

    protected $fillable = [
        'uid',
        'inbox_messages_id',
        'status',
        'attachment_status',
        'custom_attachments',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'custom_attachments' => 'array',
    ];

    public function inbox()
    {
        return $this->belongsTo(InboxMessages::class, 'inbox_messages_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }
}
