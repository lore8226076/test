<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserEquipmentSession;

class UserEquipmentAttribute extends Model
{
    protected $table = 'user_equipment_attributes';

    protected $fillable = [
        'uid',
        'equipment_id',
        'attribute_name',
        'attribute_value'
    ];

    protected $hidden = [
        'id',
        'uid',
        'equipment_id',
        'created_at',
        'updated_at',
    ];

    /**
     * 獲取關聯的裝備
     */
    public function equipment()
    {
        return $this->belongsTo(UserEquipmentSession::class, 'equipment_id', 'id');
    }

}
