<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgameStages extends Model
{
    use HasFactory;

    protected $table = 'gddb_surgame_stages';
    public $timestamps = false;
    protected $fillable = [
        'map_key_name',
        'scene_name',
        'scene_logic',
        'scene_ui',
    ];

    protected $casts = [
        'map_key_name' => 'string',
        'scene_name' => 'string',
        'scene_logic' => 'string',
        'scene_ui' => 'string',
    ];
}
