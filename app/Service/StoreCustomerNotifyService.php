<?php
namespace App\Service;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use App\Models\Stores;
use App\Models\StoreCustomerNotifies;
use App\Models\StoreEmployeeCategory;

use Carbon\Carbon;

class StoreCustomerNotifyService extends AppService
{
    //XGATE
    public function __construct(){

    }

    public static function agreeSv($order){
        $content = '工程單號['.$order->no.']，您的SV項目已通過批准，請您盡速完成接單作業，否則系統將會取消此次SV結果';
        self::add($order->store_customer_id, 1, $content, $order->id);
    }
    public static function agreeRedate($order){
        $content = '工程單號['.$order->no.']，更改作業日期已通過批准，提醒您請提前準備與準時到場處理作業';
        self::add($order->store_customer_id, 1, $content, $order->id);
    }
    public static function rejectRedate($order){
        $content = '工程單號['.$order->no.']，更改作業日期申請不通過';
        self::add($order->store_customer_id, 1, $content, $order->id);
    }
    public static function agreeFinish($order){
        $content = '工程單號['.$order->no.']，已通過驗收，請您盡速完成簽署作業，以便為您進行撥款作業';
        self::add($order->store_customer_id, 1, $content, $order->id);
    }

    public static function add($store_customer_id, $type, $content, $order_id = null){
        $storeCustomerNotify = new StoreCustomerNotifies;
        $storeCustomerNotify->store_customer_id = $store_customer_id;
        $storeCustomerNotify->type = $type;
        $storeCustomerNotify->content = $content;
        $storeCustomerNotify->order_id = $order_id;
        $storeCustomerNotify->save();
    }
}