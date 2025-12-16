<?php
namespace App\Models;

use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Follows extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'follows';

    protected $fillable = [
        'follower_uid',
        'following_uid',
        'note',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'id'
    ];

    public function follower()
    {
        return $this->belongsTo(Users::class, 'follower_uid', 'uid');
    }

    public function following()
    {
        return $this->belongsTo(Users::class, 'following_uid', 'uid');
    }
}
