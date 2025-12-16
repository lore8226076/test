<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCharacter extends Model
{
    use HasFactory;

    protected $table = 'user_characters';

    protected $fillable = [
        'uid',
        'character_id',
        'star_level',
        'has_use',
        'slot_index',
    ];

    protected $casts = [
        'uid' => 'integer',
        'character_id' => 'integer',
        'star_level' => 'integer',
        'has_use' => 'integer',
        'slot_index' => 'integer',
    ];

    protected $hidden = [
        'uid', 'created_at', 'updated_at', 'id',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }

    public function character()
    {
        return $this->belongsTo(GddbSurgameHeroes::class, 'character_id', 'unique_id');
    }

    /**
     * 獲得英雄
     *
     * * @return array
     * - status: 'created' (成功建立新英雄)
     * - status: 'exists'  (已擁有，需要 Service 轉發碎片)
     */
    public static function tryObtain($uid, $characterId)
    {
        // 1. 檢查是否已擁有
        $character = self::where('uid', $uid)
            ->where('character_id', $characterId)
            ->first();

        if ($character) {
            // 已擁有，回傳讓 Service 去發碎片
            return [
                'status' => 'exists',
                'character' => $character,
                'fragment_id' => GddbSurgameHeroes::getFragmentIdByCharacterId($characterId),
            ];
        }

        $newCharacter = self::create([
            'uid' => $uid,
            'character_id' => $characterId,
            'star_level' => 0,
            'slot_index' => null,
        ]);

        return [
            'status' => 'created',
            'character' => $newCharacter,
        ];
    }
}
