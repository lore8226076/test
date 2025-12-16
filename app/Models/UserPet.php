<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Users;

class UserPet extends Model
{
    use SoftDeletes;

    protected $table = 'user_pets';

    protected $fillable = [
        'uid',
        'pet_id',
        'pet_name',
        'pet_str',
        'pet_def',
        'pet_sta',
        'pet_exp',
        'pet_level',
        'pet_unallocated_points',
        'pet_skin_id',
        'deleted_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'pet_skin_id',
    ];

    // user
    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }
}
