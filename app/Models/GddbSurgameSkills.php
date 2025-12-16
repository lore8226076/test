<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgameSkills extends Model
{
    use HasFactory;

    protected $table = 'gddb_surgame_skills';
    public $timestamps = false;
    protected $fillable = [
        'ui_sprite_name',
        'prefab',
    ];

    protected $casts = [
        'ui_sprite_name' => 'string',
        'prefab' => 'string',
    ];
}
