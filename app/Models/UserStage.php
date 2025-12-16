<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStage extends Model
{
    use HasFactory;

    protected $table = 'user_stages';

    protected $fillable = [
        'uid',
        'stage_id',
        'is_clear',
    ];
}
