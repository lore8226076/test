<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

use App\Models\StoreEmployees;
use App\Models\StoreSettings;

use Carbon\Carbon;

class Stores extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'stores';
    // protected $primaryKey = 'id';
    // use SoftDeletes;

    protected $hidden = [
        'password',
    ];

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];
    // protected $fillable = [];

    protected $appends = ['booking_url', 'login_url', 'logo_url', 'is_active_text', 'openings', 'expriy_day'];

    protected $_virtual = ['login_url', 'logo_url', 'is_active_text', 'openings', 'expriy_day'];

    public function getBookingUrlAttribute()
    {
        return env('SITE_FRAME_DOMAIN').'/'.$this->encode_id;
    }

    public function getLoginUrlAttribute()
    {
        return route('store.store.employee_login',base64_encode($this->id));
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path?Storage::disk('s3')->url($this->logo_path):'';
    }

    public function getIsActiveTextAttribute()
    {
        return $this->is_active?__('是'):__('否');
    }

    public function getOpeningsAttribute()
    {
        $openings = [
            'week1'=> ['is_active'=>false, 'start'=>'', 'end'=>'', 'week'=>1],
            'week2'=> ['is_active'=>false, 'start'=>'', 'end'=>'', 'week'=>2],
            'week3'=> ['is_active'=>false, 'start'=>'', 'end'=>'', 'week'=>3],
            'week4'=> ['is_active'=>false, 'start'=>'', 'end'=>'', 'week'=>4],
            'week5'=> ['is_active'=>false, 'start'=>'', 'end'=>'', 'week'=>5],
            'week6'=> ['is_active'=>false, 'start'=>'', 'end'=>'', 'week'=>6],
            'week7'=> ['is_active'=>false, 'start'=>'', 'end'=>'', 'week'=>7],
        ];
        if($this->opening){
            $openings = json_decode($this->opening, true);
        }
        return $openings;
    }

    public function getExpriyDayAttribute(){
        $expriy_day = 0;
        if(!empty($this->expriy_date) && $this->expriy_date > Carbon::now()->toDateString()){
            $carbonExpriyDate = Carbon::parse($this->expriy_date);
            $dateDiff = $carbonExpriyDate->diff(Carbon::now());
            $expriy_day = $dateDiff->days;
        }
        return $expriy_day;
    }

    protected static function boot()
    {
        parent::boot();

        // creating, created, saving, saved, deleting, deleted
        static::saved(function ($entity) {
            $dirty = $entity->getDirty();
            if($entity->exists() && isset($dirty['password'])){
                $storeEmployee = StoreEmployees::where([['store_id',$entity->id], ['account',$entity->account]])->first();
                if($storeEmployee){
                    $storeEmployee->password = $entity->password;
                    $storeEmployee->save();
                }
            }

            // if($entity->exists() && !empty($entity->getOriginal('logo_path'))){
                // if(Storage::disk('s3')->exists($entity->getOriginal('logo_path'))){
                //     Storage::disk('s3')->delete($entity->getOriginal('logo_path'));
                // }
            // }
        });

        static::created(function ($store) {
            $storeEmployee = new StoreEmployees;
            $storeEmployee->account = $store->account;
            $storeEmployee->password = $store->password;
            $storeEmployee->store_id = $store->id;
            $storeEmployee->cellphone = $store->cellphone;
            $storeEmployee->save();

            $storeSetting = new StoreSettings;
            $storeSetting->store_id = $store->id;
            $storeEmployee->save();

            // $userLevel = new UserLevels;
            // $userLevel->store_id = $store->id;
            // $userLevel->name = __('一般會員');
            // $dealerLevel->auto_up = 0;
            // $userLevel->save();

            // $dealerLevel = new DealerLevels;
            // $dealerLevel->store_id = $store->id;
            // $dealerLevel->name = __('初階經銷');
            // $dealerLevel->auto_up = 0;
            // $dealerLevel->save();
        });

    }

    public function store_banners(){
        return $this->hasMany('App\Models\StoreFiles', 'store_id')
                ->where('type','store_banner')
                ->where('start_date', '<=', Carbon::now()->toDateString())
                ->where('end_date', '>=', Carbon::now()->toDateString())
                ->orderBy('sort');
    }

    public function storeSetting()
    {
        return $this->hasOne('App\Models\StoreSettings', 'store_id');
    }

    // public function user_levels()
    // {
    //     return $this->hasMany('App\Models\UserLevels', 'store_id')->where('is_active',1)->orderBy('sort');
    // }
    // public function store_files()
    // {
    //     return $this->hasMany('App\Models\StoreFiles', 'store_id')->orderBy('sort');
    // }

    // public function logo_image()
    // {
    //     return $this->hasOne('App\Models\StoreFiles', 'store_id')->where('type', '=', 'logo')->latest();
    // }
    // public function webicon_image()
    // {
    //     return $this->hasOne('App\Models\StoreFiles', 'store_id')->where('type', '=', 'webicon')->latest();
    // }
    // public function seologo_image()
    // {
    //     return $this->hasOne('App\Models\StoreFiles', 'store_id')->where('type', '=', 'seologo')->latest();
    // }
}
