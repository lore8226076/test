<?php
namespace App\Models;

use App\Models\UserMaps as Map;
use Illuminate\Database\Eloquent\Model;

class MaterialStage extends Model
{
    protected $table = 'material_stages';

    protected $fillable = [
        'name',
        'localization_name',
        'description',
        'category_id',
        'map_id',
        'sort',
        'is_active',
        'stamina_cost',
        'image_path', // 圖片
        'random_reward_items_rate', // 隨機獎勵機率
        'random_reward_count', // 隨機獎勵數量
        'random_reward', // 隨機獎勵
        'fixed_reward', // 固定獎勵
        'player_level', // 玩家等級
        'prev_stage_id', // 前一關卡

        'map_file_path',
        'map_file_name',
    ];

    protected $casts = [
        'reward_items' => 'array',
        'reward_items_rate' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'is_active',
        'sort',
    ];

    public function category()
    {
        return $this->belongsTo(MaterialStageCategory::class, 'category_id', 'id');
    }

    public function map()
    {
        return $this->belongsTo(Map::class, 'map_id', 'id');
    }
}
