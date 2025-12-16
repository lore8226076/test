<?php
namespace App\Models;

use App\Models\InboxMessages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboxAttachments extends Model
{
    use HasFactory;

    protected $table = 'inbox_attachments';

    protected $fillable = [
        'inbox_messages_id',
        'item_id',
        'amount',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function inbox()
    {
        return $this->belongsTo(InboxMessages::class, 'inbox_messages_id', 'id');
    }

}
