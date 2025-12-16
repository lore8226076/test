<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GddbLocalizationName extends Model
{
    use HasFactory;

    protected $table = 'gddb_localization_names';
    protected $fillable = ['key', 'en_info', 'zh_info'];

    // 道具表
    public function gddbItme()
    {
        return $this->belongsTo('App\Models\GddbItems', 'key', 'localization_name');
    }
}
