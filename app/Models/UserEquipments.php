<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UserEquipments extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'user_equipments';
    // protected $primaryKey = 'id';
    // use SoftDeletes;

    protected $hidden = [];

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];
    // protected $fillable = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [];

    protected $_virtual = [];


    protected static function boot()
    {
        parent::boot();
        // creating, created, saving, saved, deleting, deleted
        static::creating(function ($entity) {
        });
        static::saving(function ($entity) {
        });
        static::saved(function ($entity) {
        });
        static::deleting(function ($entity) {
        });
    }

    public function getAvaColorsAttribute($value)
    {
        return $value && is_string($value)?json_decode($value, true):$value;
    }

    public function getColorIndexAttribute($value)
    {
        return $value && is_string($value)?json_decode($value, true):$value;
    }

    public function getHeadSetAttribute($value)
    {
        return $value && is_string($value)?json_decode($value, true):$value;
    }

    public function getBackSetAttribute($value)
    {
        return $value && is_string($value)?json_decode($value, true):$value;
    }
}
