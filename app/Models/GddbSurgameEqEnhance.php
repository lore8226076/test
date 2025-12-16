<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GddbSurgameEqEnhance extends Model
{
    protected $table    = 'gddb_surgame_eq_enhance';
    public $timestamps  = false;
    protected $fillable = [
        'lv',
        'min_slot_lv',
        'cost',
        'cost_amount',
        'ex_cost',
        'ex_cost_amount',
    ];
}
