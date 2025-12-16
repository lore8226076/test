<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbCharLevels extends Model
{
    use HasFactory;
    protected $table = 'gddb_char_levels';

    protected $fillable = [
        'lv',
        'exp',
        'hp',
        'bp',
        'sp',
        'atk',
        'def',
        'brk',
    ];
}
