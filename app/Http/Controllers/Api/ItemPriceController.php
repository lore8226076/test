<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemGroup;
use App\Models\ItemPrices;
use App\Models\UserFirstPurchaseRecord;
use App\Service\ErrorService;
use App\Service\MonthCardService;
use App\Service\UserItemService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ItemPriceController extends Controller
{
    public function __construct(Request $request)
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['getByItemId', 'getByManagerId', 'getItems', 'getCashItems']]);
        }
    }

    public function getByItemId(Request $request, $tag, $item_id)
    {
        if (empty($tag)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:00013'), 422);
        }

        if (empty($item_id)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0004'), 422);
        }

        $itemPrice = ItemPrices::where('tag', $tag)->where('item_id', $item_id)->first();
        if (empty($itemPrice)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 422);
        }

        return response()->json(['data' => $itemPrice], 200);
    }

    public function getByManagerId(Request $request, $tag, $region, $manager_id)
    {
        if (empty($tag)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:00013'), 422);
        }

        if (empty($region)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0005'), 422);
        }

        if (empty($manager_id)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0004'), 422);
        }

        $item = UserItemService::getItemByManagerId($region, $manager_id);

        if (empty($item['item_id'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0009'), 422);
        }

        $itemPrice = ItemPrices::where('tag', $tag)->where('item_id', $item['item_id'])->first();
        if (empty($itemPrice) && $tag == 'Color' && $region == 'Avatar') {
            $itemPrice = new ItemPrices;
            $itemPrice->id = 0;
            $itemPrice->item_id = (int) $item['item_id'];
            $itemPrice->tag = 'Color';
            $itemPrice->currency_item_id = 100;
            $itemPrice->price = 1000;
            $itemPrice->discount_percentage = 0;
            $itemPrice->price_after_discount = null;
            $itemPrice->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $itemPrice->updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $itemPrice->save();
        }

        if (empty($itemPrice)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 422);
        }

        return response()->json(['data' => $itemPrice], 200);
    }

    // 取得全部商品
    public function getItems()
    {
        // 取得群組道具Ids
        $itemGroups = ItemGroup::select('item_id', 'parent_item_id', 'qty')
            ->get()
            ->groupBy('parent_item_id')
            ->map(function ($group) {
                return $group->pluck('qty', 'item_id')->toArray();
            })
            ->toArray();

        $itemPrice = Cache::get('all_item_prices');
        if (! $itemPrice) {
            // 排除的Tag關鍵字
            $excludeTags = ['Color', 'color'];
            $itemPrice = ItemPrices::whereNotIn('tag', $excludeTags)->get();
            if ($itemPrice->isEmpty()) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 422);
            }
            // 如果是群組道具給予群組, 否則給[]
            foreach ($itemPrice as $item) {
                if (array_key_exists($item->item_id, $itemGroups)) {
                    $item->group_items = $itemGroups[$item->item_id];
                } else {
                    $item->group_items = [];
                }
            }

            // 給予main tag
            foreach ($itemPrice as $item) {
                $item->main_tag = $this->getItemSubTag($item->tag);
            }

            Cache::put('all_item_prices', $itemPrice, 600); // 10 分鐘
        }

        return response()->json(['data' => $itemPrice], 200);
    }

    // 取得純現金商品
    public function getCashMallItems(Request $request)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        // 現金商品
        $items = ItemPrices::whereNotNull('product_id')->
        whereIn('tag', ['cash', 'Cash'])->with([
            'itemInfo:item_id,localization_name',
            'itemInfo.itemLocalization:key,en_info,zh_info',
        ])
            ->get(['item_id', 'tag', 'product_id', 'price', 'qty', 'tag_localization_name', 'discount_percentage', 'price_after_discount']);

        $items->each(function ($item) use ($user) {
            $item->main_tag = $this->getItemSubTag($item->tag);
            $item->has_first_purchase_rewards = $this->checkFirstPurchaseRecord($user->uid, $item->product_id);
        });

        // 現金道具禮包

        // 月卡
        $monthCards = MonthCardService::getUserMonthCardStatus($user->uid);

        $results = [
            'items' => $items,
            'month_cards' => $monthCards,
        ];

        return response()->json(['data' => $results], 200);
    }

    // 檢查鑽石首購紀錄
    private function checkFirstPurchaseRecord($uid, $productId)
    {
        return UserFirstPurchaseRecord::where('uid', $uid)
            ->where('product_id', $productId)
            ->exists() ? -1 : 1;
    }

    // 網頁產品分頁
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $itemPrices = new ItemPrices;

        $data = $request->input();

        foreach ($data as $field => $value) {
            $field = str_replace('__', '.', $field);
            switch ($field) {
                case 'per_page':
                case 'to_key_value':
                case 'current_page':
                case 'sort':
                case 'direction':
                case '_token':
                    break;
                case 'item_id':
                    if (! empty($value)) {
                        $itemPrices = $itemPrices->whereRaw('LOWER('.$field.') like ?', ['%'.strtolower($value).'%']);
                    }
                    break;
                default:
                    if (! empty($value)) {
                        $itemPrices = $itemPrices->where($field, $value);
                    }
                    break;
            }
        }

        if (empty($data['sort'])) {
            $data['sort'] = (new ItemPrices)->getTable().'.id';
        }
        $sortField = $data['sort'];
        $sortDirection = ! empty($data['direction']) ? $data['direction'] : 'asc';
        $itemPrices = $itemPrices->orderBy($sortField, $sortDirection);

        if ($perPage == 0) {
            $itemPrices = $itemPrices->get();
        } else {
            $current_page = empty($data['current_page']) ? 1 : $data['current_page'];
            $itemPrices = $itemPrices->paginate($perPage, ['*'], 'page', $current_page);
        }

        if (isset($data['to_key_value'])) {
            $itemPrices = $itemPrices->get();
            $to_key_value = explode('-', $data['to_key_value']);
            $temp = [];
            foreach ($itemPrices as $user) {
                $temp[$user->{$to_key_value[0]}] = $user->{$to_key_value[1]};
            }
            $itemPrices = $temp;
        }

        return response()->json([
            'data' => $itemPrices,
            'dd' => $data,
        ], 200);
    }

    // 取得現金商品
    public function getCashItems()
    {
        $items = ItemPrices::whereIn('tag', ['cash', 'Cash'])->with([
            'itemInfo:item_id,localization_name',
            'itemInfo.itemLocalization:key,en_info,zh_info',
        ])->get();

        return response()->json(['data' => $items]);
    }

    // 透過tag取得main tag
    private function getItemSubTag($tag)
    {
        $mainTags = $this->getItemMainTag();
        foreach ($mainTags as $mainTag => $subTags) {
            if (in_array(strtolower($tag), array_map('strtolower', $subTags))) {
                return $mainTag;
            }
        }

        return null;
    }

    private function getItemMainTag()
    {
        return [
            'cash' => ['Cash'],
            'furniture' => [
                'furniture packs',
                'room furniture',
                'tables & seating',
                'cabinets & storage',
            ],
            'other' => [
                'dress-up closet',
                'deco & plants',
                'wall decor',
                'misc & props',
                'traps & mechanisms',
                'bundles & packs',
            ],
        ];
    }
}
