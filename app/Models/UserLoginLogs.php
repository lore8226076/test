<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginLogs extends Model
{
    use HasFactory;

    protected $table = 'user_login_logs';

    protected $fillable = ['uid', 'ip', 'methods', 'login_data'];
}
