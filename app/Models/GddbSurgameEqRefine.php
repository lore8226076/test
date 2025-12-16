<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GddbSurgameEqRefine extends Model
{
    protected $table    = 'gddb_surgame_eq_refine';
    public $timestamps  = false;
    protected $fillable = [
        'lv',
        'min_enhance_lv',
        'cost',
        'cost_amount',
        'success_rate',
    ];
}
