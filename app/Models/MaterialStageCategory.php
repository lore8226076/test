<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialStageCategory extends Model
{
    protected $table = 'material_stage_categories';

    protected $fillable = [
        'name',
        'localization_name',
        'parent_id',
        'sort',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'timestamp',
        'end_time'   => 'timestamp',
    ];

    protected $hidden = [
        'start_time',
        'end_time',
        'is_active',
        'created_at',
        'updated_at',
        'sort'
    ];

    // 自關聯
    public function parent()
    {
        return $this->belongsTo(MaterialStageCategory::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(MaterialStageCategory::class, 'parent_id', 'id');
    }

    public function materialStages()
    {
        return $this->hasMany(MaterialStage::class, 'category_id', 'id');
    }
}
