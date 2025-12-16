<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSkills extends Model
{
    use HasFactory;

    protected $table = 'gddb_skills';

    protected $fillable = [
        'skill_id',
        'name',
        'desc',
        'ui_sprite_name',
        'cool_down',
        'anim_index',
        'active_delay',
        'active_timeout',
        'distance',
        'protection_time',
        'hit_reaction',
        'hit_dir_mode',
        'knockup_speed_xz',
        'knockup_speed_y',
        'damage',
        'minimum_damage_limit',
        'minimum_damage_value',
        'proj_attach_on_bone',
        'proj_attach_bone_name',
        'proj_prefab',
        'proj_explosion_damage',
        'proj_tracking_mode',
        'parabola',
        'proj_destory_skill_id',
        'proj_destory_npc_id',
    ];
}
