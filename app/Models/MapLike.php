<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapLike extends Model
{
    use HasFactory;

    protected $table = 'map_likes';

    protected $fillable = ['uid', 'map_id'];

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }
}
