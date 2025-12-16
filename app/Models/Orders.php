<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

use App\Service\OrderService;
use App\Service\StoreCustomerNotifyService;
use Carbon\Carbon;

class Orders extends BaseModel
{
    // protected $connection = 'connection-name';
    protected $table = 'orders';
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
        'finish_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'repair_type' => 0,
        'place_type' => 0,
        'need_man' => 1,
        'city_area_list_id' => 0,
        'contact_gender' => 0,
        'status' => 0,
        'is_crash' => 0,
        'is_night' => 0,
        'need_sv' => 0,
        'fees' => [
            [
                'day' => 1,
                'fee' => 0,
            ],
            [
                'day' => 7,
                'fee' => 0,
            ],
            [
                'day' => 14,
                'fee' => 0,
            ],
            [
                'day' => 30,
                'fee' => 0,
            ],
        ],
        'sv_items' => '',
        'pre_work_start_date' => '',
        'pre_work_end_date' => '',
        'pre_work_days' => 0,
        'pre_work_hours' => 0,
    ];

    protected $appends = ['status_text', 'repair_type_text', 'place_type_text',
                            'pay_date_red',
                            'fee_report_url', 'document_url', 'sv_url'];

    protected $_virtual = ['status_text', 'repair_type_text', 'place_type_text',
                            'pay_date_red',
                            'fee_report_url', 'document_url', 'sv_url'];

    public static function getSvItemV1(){
        return [
            ["name"=>"0104 放線長度(Cat5)", "value"=>""],
            ["name"=>"0214 放線長度(drop cable光纖)", "value"=>""],
            ["name"=>"1304 - 開天花/開舊有石膏板/地枱板", "value"=>""],
            ["name"=>"0901 - 20mm膠硬喉", "value"=>""],
            ["name"=>"0915 - 20mm膠軟喉", "value"=>""],
            ["name"=>"0918 - 20mm鐵軟喉", "value"=>""],
            ["name"=>"0907 - 20mm鐵喉", "value"=>""],
            ["name"=>"0701 - 32mm梳槽孔", "value"=>""],
            ["name"=>"0712 - 25mm鑽孔", "value"=>""],
            ["name"=>"1201 - 梯台", "value"=>""],
            ["name"=>"1202 - 高台", "value"=>""],
            ["name"=>"1303 - 夜工", "value"=>""],
        ];
    }
    public static function getStatuses(){
        return [
            0 => __('草稿'),
            1 => __('發佈中'),
            2 => __('待SV'),
            3 => __('待報價'),
            4 => __('待師傅確認報價'),
            6 => __('申請改期'),
            7 => __('待施工'),
            8 => __('施工中'),
            9 => __('施工完成'),
            10 => __('待師傅簽署'),
            11 => __('待撥款'),
            12 => __('結案'),
        ];
    }
    public function getStatusTextAttribute()
    {
        return isset($this->getStatuses()[$this->status])?$this->getStatuses()[$this->status]:$this->status;
    }
    public static function getRepairTypes(){
        return [
            1 => __('寬頻'),
            2 => __('電話'),
            3 => __('光纖'),
        ];
    }
    public function getRepairTypeTextAttribute()
    {
        return isset($this->getRepairTypes()[$this->repair_type])?$this->getRepairTypes()[$this->repair_type]:$this->repair_type;
    }
    public static function getPlaceTypes(){
        return [
            1 => __('住宅安裝工程'),
            2 => __('商業安裝工程'),
        ];
    }
    public function getPlaceTypeTextAttribute()
    {
        return isset($this->getPlaceTypes()[$this->place_type])?$this->getPlaceTypes()[$this->place_type]:$this->place_type;
    }
    public function getFeeReportUrlAttribute()
    {
        return $this->fee_report?Storage::disk('s3')->url($this->fee_report):'';
    }
    public function getDocumentUrlAttribute()
    {
        return $this->document_path?Storage::disk('s3')->url($this->document_path):'';
    }
    public function getSvUrlAttribute()
    {
        return $this->sv_path?Storage::disk('s3')->url($this->sv_path):'';
    }
    public function getPayDateRedAttribute()
    {
        $today = Carbon::now();
        $pay_date = Carbon::parse($this->pay_date);
        // 检查是否是星期五
        if ($pay_date <= $today) {
            return true;
        }

        if ($today->dayOfWeek === Carbon::FRIDAY) {
            if ($pay_date->addDays(2)->dayOfWeek >= Carbon::SATURDAY) {
                return true;
            }
        }
        return false;
    }


    public function getSvItemsAttribute($value)
    {
        return $value && is_string($value)?json_decode($value, true):$value;
    }
    public function getFeesAttribute($value)
    {
        return $value && is_string($value)?json_decode($value, true):$value;
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
            if($entity->isDirty('status')){
                if($entity->getOriginal('status')==3 && $entity->status==4){
                    StoreCustomerNotifyService::agreeSv($entity);
                }
                if($entity->getOriginal('status')==9 && $entity->status==10){
                    StoreCustomerNotifyService::agreeFinish($entity);
                }
            }
        });
        static::deleting(function ($entity) {
        });
    }

    public function cityAreaList()
    {
        return $this->belongsTo('App\Models\CityAreaLists', 'city_area_list_id');
    }

    public function storeCustomer()
    {
        return $this->belongsTo('App\Models\StoreCustomers', 'store_customer_id');
    }

    public function orderSvFiles(){
        return $this->hasMany('App\Models\OrderSvFiles', 'order_id');
    }

    public function orderCheckins(){
        return $this->hasMany('App\Models\OrderCheckins', 'order_id');
    }

    public function orderSignReceiptFiles(){
        return $this->hasMany('App\Models\OrderSignReceiptFiles', 'order_id');
    }

    public function orderFinishFiles(){
        return $this->hasMany('App\Models\OrderFinishFiles', 'order_id');
    }

    public function orderApplyUpdate(){
        return $this->hasOne('App\Models\OrderApplyUpdates', 'order_id')->where('status', 0);
    }
}
