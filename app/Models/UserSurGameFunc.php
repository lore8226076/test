<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users;

class UserSurGameFunc extends Model
{
    use HasFactory;

    protected $table = 'user_surgame_funcs';

    public $timestamps = false;

    protected $fillable = [
        'uid',
        'func_key',
    ];

    protected $hidden = [
        'id',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }
}
