<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tasks;
use App\Models\GddbSurgameGrade;

class UserTasks extends Model
{
    protected $table = 'user_tasks';

    protected $fillable = [
        'uid',
        'task_id',
        'status',
        'progress',
        'completed_at',
        'reward_status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'completed_at' => 'timestamp',
        'reward_status' => 'boolean',
        'progress' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id', 'id');
    }
}
