<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreMenus extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'store_menus';
    // protected $primaryKey = 'id';
    // use SoftDeletes;
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        // creating, created, saving, saved, deleting, deleted
        static::saved(function ($entity) {

        });
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'up_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'up_id')->orderBy('sort');
    }
}
