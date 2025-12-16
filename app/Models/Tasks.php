<?php
namespace App\Models;

use App\Models\TaskCategory;
use App\Models\UserTasks;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    protected $table = 'tasks';

    protected $fillable = [
        'id',
        'localization_name',
        'summary',
        'description',
        'category_id',
        'type',
        'condition',
        'check_id',
        'reward',
        'start_at',
        'end_at',
        'prev_task_id',
        'next_task_id',
        'is_auto_complete',
        'repeatable_type',
        'is_active',
        'auto_assign',
        'series_id',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'is_auto_complete' => 'boolean',
        'start_at'         => 'timestamp',
        'end_at'           => 'timestamp',
        'condition'        => 'array',
        'reward'           => 'array',
        'prev_task_id'     => 'integer',
        'next_task_id'     => 'integer',
        'series_id'        => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'category', // 隱藏關聯
        'category_id',
    ];

    public function userTasks()
    {
        return $this->hasMany(UserTasks::class, 'task_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id', 'id');
    }

    public function grade()
    {
        return $this->belongsTo(GddbSurgameGrade::class, 'series_id', 'unique_id');
    }

}
