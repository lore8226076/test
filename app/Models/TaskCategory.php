<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tasks;
class TaskCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'localization_name',
        'is_active',
        'show_type',
        'show_page_prefab',
        'bonus_task_start_id',
        'bonus_task_end_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_type' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'is_active',
    ];

    protected $table = 'task_categories';

    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'category_id', 'id');
    }

}
