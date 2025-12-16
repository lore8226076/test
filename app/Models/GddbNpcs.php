<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbNpcs extends Model
{
    use HasFactory;

    protected $table = 'gddb_npcs';

    protected $fillable = [
        'enemy_id',
        'prefab',
        'search_dist',
        'strafe_min',
        'strafe_max',
        'hp',
        'bp',
        'atk',
        'def',
        'brk',
        'lv',
        'exp',
        'gold',
        'skills',
        'trigger_radius',
        'killedscore',
    ];
}
