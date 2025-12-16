<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgameHeroes extends Model
{
    use HasFactory;

    protected $table = 'gddb_surgame_heroes';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'icon',
        'card',
        'prefab',
        'skill_01',
        'skill_02',
        'skill_02_evo',
        'rarity',
        'style_group',
        'rank_up_group',
        'rank_func_group',
        'level_group',
        'chain_skill',
        'icon_main_skill',
        'icon_talent',
        'icon_passive',
        'unique_id',
        'convert_item_id',
        'element',
        'character_id',
        'replace_item_id',
    ];

    protected $casts = [
        'name' => 'string',
        'icon' => 'string',
        'card' => 'string',
        'prefab' => 'string',
        'skill_01' => 'string',
        'skill_02' => 'string',
        'skill_02_evo' => 'string',
        'rarity' => 'string',
        'style_group' => 'string',
        'rank_up_group' => 'integer',
        'rank_func_group' => 'integer',
        'level_group' => 'integer',
        'chain_skill' => 'string',
        'icon_main_skill' => 'string',
        'icon_talent' => 'string',
        'icon_passive' => 'string',
        'convert_item_id' => 'integer',
        'element' => 'integer',
        'unique_id' => 'string',
        'replace_item_id' => 'integer',
        'character_id' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(UserCharacter::class, 'character_id', 'character_id');
    }

    /**
     * 透過 Character ID 取得對應的碎片 Item ID (replace_item_id)
     */
    public static function getFragmentIdByCharacterId($characterId)
    {
        return self::where('character_id', $characterId)
            ->value('replace_item_id');
    }

    /**
     * 透過 碎片 Item ID 取得對應的 Character ID
     * 用於：當獲得道具時，反查這是哪隻英雄的碎片
     */
    public static function getCharacterIdByFragmentId($itemId)
    {
        return self::where('replace_item_id', $itemId)
            ->value('character_id');
    }

    /**
     * 透過item_id 取得 Character ID
     * 用於取得英雄item_id時 獲得實際角色id
     */
    public static function getCharacterIdByItemId($itemId)
    {
        return self::where('unique_id', $itemId)->value('character_id');
    }

    /**
     * 檢查此 Item ID 是否為英雄碎片
     */
    public static function isHeroFragment($itemId)
    {
        return self::where('replace_item_id', $itemId)->exists();
    }
}
