<?php
namespace App\Models;

use App\Models\userSurGameInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbSurgameGrade extends Model
{
    use HasFactory;

    protected $table    = 'gddb_surgame_grades';
    public $timestamps  = false;
    protected $fillable = [
        'unique_id',
        'grade_group',
        'grade_level',
        'grade_name',
        'reward',
        'func_key',
        'func_desc',
        'quests',
        'related_level',
    ];

    protected $casts = [
        'grade_level' => 'integer',
    ];

    public function userSurGameInfo()
    {
        return $this->hasOne(userSurGameInfo::class, 'grade_level', 'related_level');
    }

    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'series_id', 'unique_id');

    }
}
