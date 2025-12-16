<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelRequirements extends Model
{
    use HasFactory;
    
    protected $table = 'level_requirements';

    protected $fillable = [
        'level',
        'cost_item_id',
        'cost_amount',
        'ex_cost_item_id',
        'ex_cost_amount',
        'memo',
    ];

    protected $casts = [
        'level' => 'integer',
        'cost_item_id' => 'integer',
        'cost_amount' => 'integer',
        'ex_cost_item_id' => 'integer',
        'ex_cost_amount' => 'integer',
    ];
} 