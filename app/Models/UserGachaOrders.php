<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
class UserGachaOrders extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'user_gacha_orders';
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

    protected $appends = ['created_at_timestamp',];

    protected $_virtual = ['created_at_timestamp',];

    public function getCreatedAtTimestampAttribute()
    {
        return $this->created_at ? Carbon::parse($this->created_at)->timestamp : '';
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

    public function user()
    {
        return $this->belongsTo('App\Models\Users', 'user_id');
    }

    public function userGachaOrderDetails(){
        return $this->hasMany('App\Models\UserGachaOrderDetails', 'user_gacha_order_id');
    }


}
