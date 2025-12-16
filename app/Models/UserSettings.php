<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
class UserSettings extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'user_settings';
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

    protected $attributes = [
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

    public function getLevelDataAttribute($value)
    {
        $return_value = [];
        if($value && is_string($value)){
            $return_value = json_decode($value, true);
        }
        else{
            $return_value = self::init_level_data();
        }
        return $return_value;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Users', 'user_id');
    }


    public static function init($user_id){
        $userSetting = new UserSettings;
        $userSetting->user_id = $user_id;

        $level_data = self::init_level_data();
        $userSetting->level_data = json_encode($level_data);
        $userSetting->save();
    }

    private static function init_level_data(){
        $levelSettings = LevelSettings::orderBy('level')->orderBy('sub_level')->orderBy('section')->get();
        $level_data = [];
        foreach ($levelSettings as $levelSetting) {
            if(!isset($level_data['level_'.$levelSetting->level.$levelSetting->sub_level])){
                $level_data['level_'.$levelSetting->level.$levelSetting->sub_level] = [
                    "level"     => $levelSetting->level,
                    "sub_level" => $levelSetting->sub_level,
                    "max"   => 0,
                    "now"   => 0,
                    "section_cnt" => 0,
                    "sections" => [],
                ];
            }
            $level_data['level_'.$levelSetting->level.$levelSetting->sub_level]['sections']['section_'.$levelSetting->section] = [
                "section"   => $levelSetting->section,
                "kill"      => 0,
                "second"    => 0,
            ];
            $level_data['level_'.$levelSetting->level.$levelSetting->sub_level]['section_cnt'] = count($level_data['level_'.$levelSetting->level.$levelSetting->sub_level]['sections']);
        }
        return $level_data;
    }
}
