<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Models\UserItems;
use App\Models\UserMaps;
use App\Service\ErrorService;
use App\Service\UserItemService;
use App\Service\FileService;

use Illuminate\Http\Request;
use Validator;
use DB;

class DataCenterController extends Controller
{
    public function __construct(Request $request) {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if($referrerDomain  != config('services.API_PASS_DOMAIN')){
            $this->middleware('auth:api', ['except' => ['getItemList', 'getItem'] ]);
        }
    }

    public function getItemList(Request $request)
    {
        $itemLists = UserItemService::getItemLists();

        return response()->json(['data' => ['itemLists' => $itemLists] ], 200);
    }

    public function getItem(Request $request, $item_id)
    {
        $item = UserItemService::getItem($item_id);

        return response()->json(['data' => ['item' => $item] ], 200);
    }



}
