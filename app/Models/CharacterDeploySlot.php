<?php
namespace App\Models;

use App\Models\UserEquipmentSession;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterDeploySlot extends Model
{
    use HasFactory;

    protected $table = 'character_deploy_slots';

    protected $fillable = [
        'uid',
        'character_id',
        'level',
        'position',
    ];

    protected $case = [
        'uid'          => 'integer',
        'character_id' => 'integer',
        'level'        => 'integer',
        'position'     => 'integer',
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at',
    ];

    // 一個陣位有N件裝備
    public function equipments()
    {
        return $this->hasMany(UserEquipmentSession::class, 'slot_id', 'id');
    }

    /**
     * 關聯到使用者
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');

    }

    /**
     * 關連到陣位裝備
     */
    public function slotEquipments()
    {
        return $this->hasMany(UserSlotEquipment::class, 'slot_id', 'id');
    }
}
