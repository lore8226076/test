<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class BankLists extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'bank_lists';
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

    protected $appends = ['show_name'];

    protected $_virtual = ['show_name'];

    public function getShowNameAttribute()
    {
        return $this->bank_code .' - '. $this->name;
    }

    protected static function boot()
    {
        parent::boot();
        // creating, created, saving, saved, deleting, deleted
        static::creating(function ($entity) {
            if(empty($entity->sort)){
                // $conditions = [];
                // $conditions[] = ['store_id',$entity->store_id];
                // $conditions[] = ['is_active',1];
                // $entity->sort = BankLists::where($conditions)->count() + 1;
            }
        });
        static::saving(function ($entity) {
        });
        static::saved(function ($entity) {
        });
        static::deleting(function ($entity) {
        });
    }

}
