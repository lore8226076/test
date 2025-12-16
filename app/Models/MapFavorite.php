<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserMaps;

class MapFavorite extends Model
{
    use HasFactory;

    protected $table = 'map_favorites';

    protected $fillable = ['uid', 'map_id'];

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }

    public function map()
    {
        return $this->belongsTo(UserMaps::class, 'map_id', 'id');
    }
}
