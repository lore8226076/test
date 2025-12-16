<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgameRankFuncs extends Model
{
    use HasFactory;

    protected $table = 'gddb_surgame_rank_funcs';
    public $timestamps = false;
    protected $fillable = [
        'group_id',
        'required_star_rank',
        'name',
        'description',
        'type',
        'func_data',
        'atk_grow',
        'hp_grow',
        'def_grow',
    ];

    protected $casts = [
        'group_id' => 'integer',
        'required_star_rank' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'type' => 'string',
        'func_data' => 'string',
        'atk_grow' => 'integer',
        'hp_grow' => 'integer',
        'def_grow' => 'integer',
    ];
}
