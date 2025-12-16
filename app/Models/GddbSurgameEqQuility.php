<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GddbSurgameEqQuility extends Model
{
    protected $table      = 'gddb_surgame_eq_quility';
    public $timestamps    = false;
    protected $primaryKey = 'quility';
    public $incrementing  = false;
    protected $fillable   = [
        'quility',
        'name',
        'recycle_value',
        'ex_attr_amount',
        'ex_attr_atk',
        'ex_attr_hp',
        'ex_attr_def',
    ];

    // 每個品質對應數件裝備
    public function equipments()
    {
        return $this->hasMany(GddbSurgameEquipment::class, 'quility', 'quility');
    }
}
