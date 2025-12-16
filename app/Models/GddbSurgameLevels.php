<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgameLevels extends Model
{
    use HasFactory;

    protected $table = 'gddb_surgame_levels';
    public $timestamps = false;
    protected $fillable = [
        'group_id',
        'level',
        'base_atk',
        'base_hp',
        'base_def',
    ];

    protected $casts = [
        'group_id' => 'integer',
        'level'    => 'integer',
        'base_atk' => 'integer',
        'base_hp'  => 'integer',
        'base_def' => 'integer',
    ];

    protected $hidden = [
        'id',
    ];
}
