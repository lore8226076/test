<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GddbSurgameEqMaster extends Model
{
    protected $table    = 'gddb_surgame_eq_master';
    public $timestamps  = false;
    protected $fillable = [
        'type',
        'lv',
        'necessary_min_lv',
        'atk_bonus',
        'hp_bonus',
        'def_bonus',
    ];
}
