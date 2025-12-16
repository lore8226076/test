<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserItemLogs;
use App\Models\UserItems;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\UserItemService;
use Illuminate\Http\Request;

class UserItemController extends Controller
{
    public function __construct(Request $request)
    {
        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['getAvatars', 'getMaps']]);
        }
    }

    public function getAvatars(Request $request, $uid)
    {
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $item_ids    = [];
        $manager_ids = [];

        if ($user->is_admin) {
            $userItems = UserItemService::getItemLists();
            foreach ($userItems as $userItem) {

                if (! empty($userItem['region']) && $userItem['region'] == UserItems::REGION_AVATAR) {
                    $item_ids[]    = (int) $userItem['item_id'];
                    $manager_ids[] = (int) $userItem['manager_id'];
                }
            }
        } else {
            $userItems = UserItems::where('user_id', $user->id)->where('region', UserItems::REGION_AVATAR)->get();
            foreach ($userItems as $userItem) {
                $item_ids[] = $userItem->item_id;
                if ($userItem->manager_id > 0) {
                    $manager_ids[] = $userItem->manager_id;
                }

            }
        }

        return response()->json(['data' => ['item_ids' => $item_ids, 'manager_ids' => $manager_ids]], 200);
    }

    public function getMaps(Request $request, $uid)
    {
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $item_ids    = [];
        $manager_ids = [];

        if ($user->is_admin) {
            $userItems = UserItemService::getItemLists();
            foreach ($userItems as $userItem) {

                if (! empty($userItem['region']) && $userItem['region'] == UserItems::REGION_MAP) {
                    $item_ids[]    = (int) $userItem['item_id'];
                    $manager_ids[] = (int) $userItem['manager_id'];
                }
            }
        } else {
            $userItems = UserItems::where('user_id', $user->id)->where('region', UserItems::REGION_MAP)->get();
            foreach ($userItems as $userItem) {
                $item_ids[] = $userItem->item_id;
                if ($userItem->manager_id > 0) {
                    $manager_ids[] = $userItem->manager_id;
                }

            }
        }

        return response()->json(['data' => ['item_ids' => $item_ids, 'manager_ids' => $manager_ids]], 200);
    }

    public function giveItem(Request $request)
    {
        $data = $request->input();

        if (empty($data['uid'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $user = Users::where('uid', $data['uid'])->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        if (empty($data['item_id'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0004'), 422);
        }

        if (empty($data['qty'])) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'MallOrder:0008'), 422);
        }

        if (! isset($itemData['error'])) {
            $item = UserItemService::addItem(UserItemLogs::TYPE_SYSTEM, $user->id, $user->uid, $data['item_id'], $data['qty'], 1, '後台手動新增');

            if (empty($item['success'])) {
                return response()->json(ErrorService::errorCode(__METHOD__, $item['error_code']), 422);
            }
        }

        return response()->json(['data' => ['item' => $item]], 200);
    }

}
