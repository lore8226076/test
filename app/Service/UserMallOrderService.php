<?php
namespace App\Service;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use App\Models\Users;
use App\Models\UserItems;
use App\Models\UserItemLogs;
use App\Models\UserMallOrders;
use App\Models\Settings;

use App\Service\FileService;

use Carbon\Carbon;
use DB;

class UserMallOrderService extends AppService
{
    public static function create($user, $item, $currency_item, $itemPrice, $qty){
        $result = [
            'success' => 1,
            'error_code' => '',
        ];

        $total_price = $itemPrice->price * $qty;

        $userCurrencyItem = UserItems::where('user_id', $user->id)->where('item_id', $currency_item['item_id'])->first();
        if(empty($userCurrencyItem)) return ['success'=>0, 'error_code'=>'MallOrder:0010'];

        if($userCurrencyItem->qty < $total_price) return ['success'=>0, 'error_code'=>'MallOrder:0010'];

        $userItem = UserItems::where('user_id', $user->id)->where('item_id', $item['item_id'])->first();
        if($userItem && $userItem->region == UserItems::REGION_AVATAR){
            return ['success'=>0, 'error_code'=>'MallOrder:0012'];
        }


        DB::beginTransaction();
        try {
            $userMallOrder = new UserMallOrders;
            $userMallOrder->user_id = $user->id;
            $userMallOrder->uid = $user->uid;
            $userMallOrder->item_id = $item['item_id'];
            $userMallOrder->item_price_id = $itemPrice->id;
            $userMallOrder->qty = $qty;
            $userMallOrder->price = $itemPrice->price;
            $userMallOrder->total_price = $itemPrice->price * $qty;
            $userMallOrder->currency_item_id = $currency_item['item_id'];
            if($userMallOrder->save()){
                $result = UserItemService::addItem(UserItemLogs::TYPE_SHOP_BUY, $user->id, $user->uid, $userMallOrder->item_id, $userMallOrder->qty, 1, '商城購買', $userMallOrder->id);
                if($result['success']){
                    $currencyResult = UserItemService::addItem(UserItemLogs::TYPE_ITEM_USE, $user->id, $user->uid, $userMallOrder->currency_item_id, ($userMallOrder->total_price*-1), 1, '商城購買使用貨幣', $userMallOrder->id);
                    if($currencyResult['success']){
                        DB::commit();
                        return $result;
                    }
                    else{
                        return ['success'=>0, 'error_code'=>$currencyResult['error_code']];
                    }
                }
                else{
                    return ['success'=>0, 'error_code'=>$result['error_code']];
                }
            }
            DB::rollBack();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('商城訂單建立失敗: ' . $e->getMessage());
        }
        return ['success'=>0, 'error_code'=>'other'];
    }
    // 取得群組道具的道具名稱
    private static function getItemGroupIds(){
        $itemGroupsIds = [];
        $itemGroups = ItemGroup::get();
        foreach($itemGroups as $itemGroup){
            if (!in_array($itemGroup->parent_item_id, $itemGroupsIds)) {
                $itemGroupsIds[] = $itemGroup->parent_item_id;
            }
        }
        return $itemGroupsIds;
    }
}
