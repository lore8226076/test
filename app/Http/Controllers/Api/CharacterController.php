<?php

namespace App\Http\Controllers\Api;

use App\Models\CharacterStarRequirements;
use App\Models\LevelRequirements;
use App\Models\UserCharacter;
use App\Models\Users;
use App\Models\UserSurGameInfo;
use App\Service\CharacterService;
use App\Service\CharacterStarService;
use App\Service\ErrorService;
use App\Service\GradeTaskService;
use App\Service\TaskService;
use App\Service\UserItemService;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    public function __construct(Request $request)
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['getStarRequirements', 'getLevelRequirements', 'getAllCharacter']]);
        }
    }

    // 角色星級提升
    public function startLevelUp(Request $request)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $characterId = $request->input('character_id');
        if (! ctype_digit((string) $characterId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'CHARACTER:0001'), 422);
        }

        $existUserCharacter = UserCharacter::with(['character', 'user', 'user.userItems'])
            ->where([
                ['uid', '=', $user->uid],
                ['character_id', '=', $characterId],
            ])
            ->first();

        if (! $existUserCharacter) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'CHARACTER:0001'), 422);
        }
        // 檢查是否已經達到最高星級
        $maxStarLevel = CharacterStarService::getMaxStarLevel($existUserCharacter->character_id);
        if ($existUserCharacter->star_level >= $maxStarLevel) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'CharacterRank:0001'), 422);
        }

        // 檢查星級材料
        $starLevel = $existUserCharacter->star_level + 1;
        $starMaterial = CharacterStarService::getStarMaterial($existUserCharacter->character_id, $starLevel);
        // 檢查星級材料是否存在
        if (! $starMaterial) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'CharacterRank:0002'), 422);
        }
        // 檢查星級材料是否足夠
        $isEnough = CharacterStarService::checkStarMaterial($existUserCharacter->character_id, $starLevel, $existUserCharacter->user);
        if (! $isEnough) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'CharacterRank:0003'), 422);
        }

        // // 扣除星級材料
        $userItemService = new UserItemService;
        $userItemService->removeItem(50, $existUserCharacter?->user?->id, $existUserCharacter?->user->uid, $starMaterial['base_item_id'], $starMaterial['base_item_amount'], 1, '角色星級提升');
        if (! empty($starMaterial['extra_item_id']) && ! empty($starMaterial['extra_item_amount'])) {
            $userItemService->removeItem(50, $existUserCharacter?->user?->id, $existUserCharacter?->user->uid, $starMaterial['extra_item_id'], $starMaterial['extra_item_amount'], 1, '角色星級提升');
        }

        // 遞增星級
        $existUserCharacter->increment('star_level', 1);

        // 重新取得最新資料並隱藏 uid
        $existUserCharacter->refresh()->makeHidden(['uid']);
        unset($existUserCharacter->user);
        unset($existUserCharacter->character);
        // 如果slot_index = null 改為-1
        if (is_null($existUserCharacter->slot_index)) {
            $existUserCharacter->slot_index = -1;
        }

        // 任務系統
        $formattedTaskResult = $this->getAllTaskStatus($user);

        return response()->json(['data' => $existUserCharacter, 'finishedTasks' => $formattedTaskResult]);
    }

    public function obtainCharacter(Request $request, CharacterService $characterService)
    {
        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $characterId = $request->input('character_id');
        if (! ctype_digit((string) $characterId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'CHARACTER:0001'), 422);
        }

        // 使用共用方法獲得英雄
        $result = CharacterService::obtainCharacter($user->id, $user->uid, $characterId, '手動獲得英雄');

        if (! $result['success']) {
            return response()->json(ErrorService::errorCode(__METHOD__, $result['error_code']), 500);
        }

        $formattedTaskResult = $this->getAllTaskStatus($user);

        return response()->json([
            'data' => [
                'character' => $result['character'],
                'already_has' => $result['already_has'],
                'reward' => $result['reward'],
            ],
            'finishedTasks' => $formattedTaskResult,
        ]);
    }

    public function getUserCharacterLists(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $userCharacterAry = CharacterService::getUserCharacterList($uid);

        return response()->json(['data' => $userCharacterAry]);
    }

    // 取得所有角色星級需求
    public function getStarRequirements()
    {
        $data = CharacterStarRequirements::all();

        return $this->makeJson(true, $data, '查詢成功');
    }

    // 取得所有等級需求
    public function getLevelRequirements()
    {
        $data = LevelRequirements::all();

        return $this->makeJson(true, $data, '查詢成功');
    }

    // 重置人物等級
    public function resetMainCharacterLevel(Request $request)
    {
        $uid = auth()->guard('api')?->user()?->uid;

        // 檢查玩家是否存在
        $user = Users::where('uid', $uid)->first();
        if (! $user || empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        // 檢查surgame資料是否存在
        $surgameInfo = UserSurGameInfo::where('uid', $uid)->first();
        if (! $surgameInfo) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SURGAME:0001'), 404);
        }

        // 重置等級
        $surgameInfo->main_character_level = 1;
        $surgameInfo->current_exp = 500;
        $surgameInfo->save();

        return response()->json(['success' => true, 'message' => '主角等級已重置'], 200);
    }

    // 重置英雄等級
    public function startLevelReset(Request $request)
    {
        $uid = auth()->guard('api')?->user()?->uid;

        // 檢查玩家是否存在
        $user = Users::where('uid', $uid)->first();
        if (! $user || empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 401);
        }

        $r = UserCharacter::where('uid', $uid)->update(['star_level' => 0]);
        if ($r === false) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'CHARACTER:0014'), 500);
        }

        return response()->json(['success' => true, 'message' => '英雄等級已重置'], 200);
    }

    private function getAllTaskStatus($user)
    {
        $userSurgameInfo = UserSurGameInfo::where('uid', $user->uid)->first();
        $gradeSerivce = new GradeTaskService;
        $gradeSerivce->autoAsignGradeTask($userSurgameInfo);
        $gradeSerivce->updateByKeyword($user, 'hero');
        $gradeSerivce->updateByKeyword($user, 'player');
        $taskService = new TaskService;
        $completedTask = $taskService->getCompletedTasks($user->uid);

        return $taskService->formatCompletedTasks($completedTask);
    }
}
