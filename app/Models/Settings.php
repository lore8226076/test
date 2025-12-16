<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
class Settings extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'settings';
    // protected $primaryKey = 'id';
    // use SoftDeletes;

    protected $hidden = ['en_value', 'created_at', 'updated_at', 'id', 'updated_name'];

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

}
