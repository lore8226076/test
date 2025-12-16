<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\GddbItems;

class GddbSurgameItemPackage extends Model
{
    protected $table = 'gddb_surgame_item_package';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'manager_id',
        'auto_use',
        'choice_box',
        'random_times',
        'use_necessary',
        'contents',
        'note'
    ];

    protected $casts = [
        'auto_use' => 'boolean',
        'choice_box' => 'boolean',
        'random_times' => 'integer',
        'use_necessary' => 'integer',
    ];

    /**
     * 取得關聯的物品資訊
     */
    public function item()
    {
        return $this->belongsTo(GddbItems::class, 'item_id', 'id');
    }

}
