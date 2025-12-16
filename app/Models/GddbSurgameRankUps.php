<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgameRankUps extends Model
{
    use HasFactory;

    protected $table = 'gddb_surgame_rank_ups';
    public $timestamps = false;
    protected $fillable = [
        'group_id',
        'character_id',
        'star_level',
        'base_item_id',
        'base_item_amount',
        'extra_item_id',
        'extra_item_amount',
    ];

    protected $casts = [
        'group_id' => 'integer',
        'character_id' => 'integer',
        'star_level' => 'integer',
        'base_item_id' => 'integer',
        'base_item_amount' => 'integer',
        'extra_item_id' => 'integer',
        'extra_item_amount' => 'integer',
    ];
}
