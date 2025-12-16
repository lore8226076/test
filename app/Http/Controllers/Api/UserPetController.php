<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserItemLogs;
use App\Models\UserItems;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\TaskService;
use App\Service\UserItemService;
use App\Service\UserPetService;
use App\Service\UserStatsService;
use Illuminate\Http\Request;

class UserPetController extends Controller
{
    public function __construct(Request $request)
    {
        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['getPets']]);
        }
    }
    // 取得玩家寵物列表
    public function getPets(Request $request, $uid)
    {
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $userPets = UserPetService::getPets($user->uid);
        if (! filled($userPets)) {
            UserPetService::init($user);
            $userPets = UserPetService::getPets($user->uid);
        }

        return response()->json([
            'data' => $userPets,
        ]);
    }

    // 寵物資訊保存
    public function update(Request $request)
    {
        $data = $request->input();
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        // 如果data只有 pet_name 就只更新寵物名稱
        if (count($data) == 2 && isset($data['pet_name']) && isset($data['pet_id'])) {
            // 更新寵物名稱
            $userPet = UserPetService::updatePetName($user, $data['pet_name'], $data['pet_id']);
            if (empty($userPet)) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'UserPet:0001'), 422);
            }

            return response()->json([
                'message' => '更新成功',
                'data'    => $userPet,
            ]);
        }

        $required = ['pet_id', 'pet_str', 'pet_def', 'pet_sta', 'pet_exp', 'pet_level', 'pet_unallocated_points'];

        foreach ($required as $field) {
            if (! array_key_exists($field, $data)) {
                return response()->json(ErrorService::errorCode(__METHOD__, 'UserPet:0003'), 422);
            }
        }

        // 把平的 cost[xxx] 改成 cost['xxx']
        $parsedCost = [];
        foreach ($data as $key => $value) {
            if (preg_match('/^cost\[(.+)\]$/', $key, $matches)) {
                $parsedCost[$matches[1]] = $value;
            }
        }
        if (! empty($parsedCost)) {
            $data['cost'] = $parsedCost;
        }

        // 扣除遊戲幣
        if (isset($data['cost']['game_coin']) && $data['cost']['game_coin'] > 0) {
            $results = $this->deductItem($user, 101, $data['cost']['game_coin']);
            if ($results !== true) {
                if ($results['success'] == 0) {
                    return response()->json($results, 422);
                }
                return response()->json(ErrorService::errorCode(__METHOD__, 'UserPet:0002'), 422);
            }
        }
        // 扣除經驗值
        if (isset($data['cost']['exp']) && $data['cost']['exp'] > 0) {
            $results = $this->deductItem($user, 199, $data['cost']['exp']);
            if ($results !== true) {
                if ($results['success'] == 0) {
                    return response()->json($results, 422);
                }
                return response()->json(ErrorService::errorCode(__METHOD__, 'UserPet:0002'), 422);
            }
        }

        $userPet = UserPetService::updatePet($user, $data);
        if (empty($userPet)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'UserPet:0001'), 422);
        }

        //============ 任務系統 ============
        // 任務Service
        $taskService = new TaskService();
        // 紀錄系統任務
        $userStatsService = new UserStatsService($taskService);
        // $userStatsService->updateByKeyword($user, 'pet_level');
        // 本次登入是否有完成任務
        $completedTask       = $taskService->getCompletedTasks($user->uid);
        $formattedTaskResult = $taskService->formatCompletedTasks($completedTask);
        //============ 任務系統 ============

        return response()->json([
            'message'      => '更新成功',
            'data'         => $userPet,
            'finishedTask' => $formattedTaskResult,
        ], 200);

    }

    // 扣除對應道具
    private function deductItem($user, $itemId, $qty)
    {
        $qty = intval($qty);

        $userItem = UserItems::where('uid', $user->uid)->where('item_id', $itemId)->first();
        if (empty($userItem)) {
            return ['success' => 0, 'message' => '道具不存在'];
        }

        if ($userItem->qty < $qty || $userItem->qty <= 0) {
            return ['success' => 0, 'message' => '道具數量不足'];
        }

        if ($itemId == 101) {
            $memo = '寵物升級扣除遊戲幣';
        } elseif ($itemId == 199) {
            $memo = '寵物升級扣除經驗值';
        }

        $results = UserItemService::removeItem(UserItemLogs::TYPE_ITEM_USE, $user->id, $user->uid, $itemId, $qty, 1, $memo);
        if (empty($results)) {
            return ['success' => 0, 'message' => '扣除道具失敗'];
        }
        if ($results['success'] != 1) {
            return $results;
        }

        return true;
    }
}
