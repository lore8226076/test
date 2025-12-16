<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class OrderSvFiles extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'order_sv_files';
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

    protected $appends = ['path_url'];

    protected $_virtual = ['path_url'];

    public function getPathUrlAttribute()
    {
        return Storage::disk('s3')->url($this->file_path.$this->file_name);
    }

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

    public function order()
    {
        return $this->belongsTo('App\Models\Orders', 'order_id');
    }
}
