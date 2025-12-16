<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapTag extends Model
{
    use HasFactory;

    protected $table = 'map_tags';

    protected $fillable = ['tag_name', 'localize_name', 'sort'];

    protected $hidden = ['created_at', 'updated_at', 'sort'];   

}
