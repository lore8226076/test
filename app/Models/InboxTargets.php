<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InboxMessages;
class InboxTargets extends Model
{
    use HasFactory;

    protected $table = 'inbox_targets';

    protected $fillable = [
        'inbox_messages_id',
        'target_uid',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    
    public function inbox()
    {
        return $this->belongsTo(InboxMessages::class, 'inbox_messages_id', 'id');
    }

}
