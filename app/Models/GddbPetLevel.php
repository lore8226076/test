<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbPetLevel extends Model
{
    use HasFactory;

    protected $table = 'gddb_pet_levels';
    protected $fillable = ['lv', 'exp', 'cost'];
}
