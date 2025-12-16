<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgameLevelUps extends Model
{
    use HasFactory;

    protected $table = 'gddb_surgame_level_ups';
    public $timestamps = false;
    protected $fillable = [
        'target_level',
        'base_item_id',
        'base_item_amount',
        'extra_item_id',
        'extra_item_amount',
    ];

    protected $casts = [
        'target_level' => 'integer',
        'base_item_id' => 'integer',
        'base_item_amount' => 'integer',
        'extra_item_id' => 'integer',
        'extra_item_amount' => 'integer',
    ];
}
