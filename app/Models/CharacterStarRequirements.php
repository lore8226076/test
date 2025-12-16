<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterStarRequirements extends Model
{
    use HasFactory;
    
    protected $table = 'character_star_requirements';

    protected $fillable = [
        'character_id',
        'star_level',
        'base_item_id',
        'base_item_amount',
        'extra_item_id',
        'extra_item_amount',
        'atk_growth',
        'hp_growth',
        'def_growth',
        'memo',
    ];

    protected $casts = [
        'character_id' => 'integer',
        'star_level' => 'integer',
        'base_item_id' => 'integer',
        'base_item_amount' => 'integer',
        'extra_item_id' => 'integer',
        'extra_item_amount' => 'integer',
        'atk_growth' => 'integer',
        'hp_growth' => 'integer',
        'def_growth' => 'integer',
    ];

} 