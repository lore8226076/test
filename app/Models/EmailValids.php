<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailValids extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'email_valids';
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

}
