<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

// use App\Models\StoreSettings;

class StoreSettings extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'store_settings';
    // protected $primaryKey = 'id';
    // use SoftDeletes;

    protected $hidden = [];

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];
    // protected $fillable = [];
    protected $casts = [
        // 'allow_booking' => 'boolean',
    ];

    protected $appends = [];

    protected $_virtual = [];

    protected $attributes = [
    ];

    protected static function boot()
    {
        parent::boot();
        // creating, created, saving, saved, deleting, deleted
        static::creating(function ($entity) {
            // if(empty($entity->sort)){
            //     $conditions = [];
            //     $conditions[] = ['store_id',$entity->store_id];
            //     $conditions[] = ['type',$entity->type];
            //     $entity->sort = StoreFiles::where($conditions)->count() + 1;
            // }
        });
        static::saving(function ($entity) {
        });
        static::saved(function ($entity) {
        });
        static::deleting(function ($entity) {
        });
    }

    public function store()
    {
        return $this->belongsTo('App\Models\Stores', 'store_id');
    }
}
