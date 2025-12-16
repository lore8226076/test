<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminPermissions extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'admin_permissions';
    // protected $primaryKey = 'id';
    // use SoftDeletes;

    // protected $guarded = [];
    protected $fillable = [
        'name',
    ];

    protected $appends = ['admin_menu_name', 'admin_menu_ids_array'];

    protected static function boot()
    {
        parent::boot();
        // creating, created, saving, saved, deleting, deleted
        static::saved(function ($adminMenu) {

        });
    }

    public function getAdminMenuNameAttribute()
    {
        $admin_menu_ids = explode('_', $this->admin_menu_ids);
        $adminMenuStr = '';
        if(count($admin_menu_ids)){
            $adminMenus = AdminMenus::whereIn('id',$admin_menu_ids)->get();
            foreach ($adminMenus as $key => $adminMenu) {
                if($adminMenuStr && $adminMenu->up_id==0){
                    $adminMenuStr .= "<br>";
                }
                $adminMenuStr .= $adminMenu->name.' ';
            }
        }
        return $adminMenuStr;
    }

    public function getAdminMenuIdsArrayAttribute()
    {
        $admin_menu_ids = explode('_', $this->admin_menu_ids);
        return $admin_menu_ids;
    }

    // public function parent()
    // {
    //     return $this->belongsTo(self::class, 'up_id');
    // }

    // public function children()
    // {
    //     return $this->hasMany(self::class, 'up_id');
    // }
}
