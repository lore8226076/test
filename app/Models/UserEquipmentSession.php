<?php
namespace App\Models;

use App\Models\CharacterDeploySlot;
use App\Models\UserEquipmentAttribute;
use App\Models\Users;
use Illuminate\Database\Eloquent\Model;

class UserEquipmentSession extends Model
{
    protected $table = 'user_equipment_sessions';

    protected $fillable = [
        'uid',
        'slot_id',
        'item_id',
        'is_used',
        'position',
    ];

    protected $casts = [
        'is_used' => 'integer',
    ];

    protected $hidden = [
        'id',
        'uid',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['equipment_uid'];

    public function getEquipmentUidAttribute()
    {
        return $this->attributes['id'];
    }

    /**
     * 獲取裝備的屬性
     */
    public function attributes()
    {
        return $this->hasMany(UserEquipmentAttribute::class, 'equipment_id', 'id');
    }
    /**
     * 裝備基礎屬性
     */
    public function baseAttributes()
    {
        return $this->belongsTo(GddbSurgameEquipment::class, 'item_id', 'item_id');
    }

    // 裝備所屬陣位
    public function deploySlot()
    {
        return $this->belongsTo(CharacterDeploySlot::class, 'slot_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }

    public function item()
    {
        return $this->belongsTo(GddbItems::class, 'item_id', 'item_id');
    }
}
