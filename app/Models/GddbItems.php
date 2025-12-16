<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbItems extends Model
{
    use HasFactory;
    protected $table = 'gddb_items';

    protected $fillable = [
        'item_id',
        'localization_name',
        'localization_description',
        'category',
        'type',
        'style',
        'price',
        'exchangable',
        'manager_id',
        'network',
        'npc_id',
        'sort_weight',
        'show',
        'subtype',
        'auto_gen',
        'region',
        'rarity',
    ];

    // 扭蛋道具
    public function gachaDetails()
    {
        return $this->hasMany('App\Moldes\GachaDetails', 'item_id', 'item_id');
    }

    // 道具使用紀錄
    public function userItemLogs()
    {
        return $this->hasMany('App\Models\UserItemLogs', 'item_id', 'item_id');
    }

    // 道具多國語系
    public function itemLocalization()
    {
        return $this->hasOne('App\Models\GddbLocalizationName', 'key', 'localization_name');
    }

    // 商城道具
    public function itemPrice()
    {
        return $this->hasOne('App\Models\ItemPrices', 'item_id', 'item_id');
    }

    // 裝備資料
    public function equipment()
    {
        return $this->hasOne(GddbSurgameEquipment::class, 'unique_id', 'manager_id');
    }

    // 角色裝備關聯
    public function userEquipments()
    {
        return $this->hasMany(UserEquipmentSession::class, 'item_id', 'item_id');
    }

    // 禮包/寶箱關聯
    public function itemPackage()
    {
        return $this->hasOne(GddbSurgameItemPackage::class, 'item_id', 'item_id');
    }
}
