<?php
namespace App\Service;

use App\Models\MaterialStage;
use App\Models\MaterialStageCategory;
use App\Models\UserStage;
use App\Models\UserStatus;

class MaterialStageService
{
    /** 取得關卡列表 */
    public function getLists($uid = null)
    {
        $data = MaterialStageCategory::with([
            'children'                => function ($query) {
                $query->select('id', 'parent_id', 'name', 'localization_name', 'start_time', 'end_time');
            },
            'children.materialStages' => function ($q) {
                $q->where('is_active', 1);
            },
        ])
            ->where('is_active', 1)
            ->where('parent_id', 0)
            ->get();

        $data->each(function ($item) use ($uid) {
            $item->children->each(function ($child) use ($uid) {
                $child->makeVisible(['start_time', 'end_time']);
                $child->makeHidden(['materialStages']);
                if ($uid) {
                    $child->material_stages = $child->materialStages->map(function ($stage) use ($uid) {
                        return $this->stageFormatter($stage, $uid);
                    });
                }
            });
        });

        return $data;
    }

    /** 取得關卡資訊 */
    public function getInfo($stageId, $uid = null)
    {
        $stage = MaterialStage::where('id', $stageId)->where('is_active', 1)->first();
        if (! $stage) {
            return null;
        }

        return $this->stageFormatter($stage, $uid);
    }

    /** 隨機取得關卡獎勵 */
    public function getRandomReward($stageId)
    {
        $stage = MaterialStage::where('id', $stageId)->where('is_active', 1)->first();
        if (! $stage) {
            return null;
        }
        $finalReward = [];

        // 隨機獎勵
        $randomReward = $stage->random_reward;
        $randomCount  = $stage->random_reward_count; // 隨機獎勵次數
        $randomReward = json_decode($randomReward, true);
        // 取得全部隨機獎勵並用weight欄位執行隨機
        $totalWeight = array_sum(array_column($randomReward, 'weight'));
        for ($i = 0; $i < $randomCount; $i++) {
            $rand          = mt_rand(1, $totalWeight);
            $currentWeight = 0;
            foreach ($randomReward as $item) {
                $currentWeight += $item['weight'];
                if ($rand <= $currentWeight) {
                    $finalReward[] = [
                        'item_id' => $item['item_id'],
                        'amount'  => $item['amount'] ?? 1,
                    ];
                    break;
                }
            }
        }
        // 加入固定獎勵
        $fixedReward = json_decode($stage->fixed_reward, true);
        $finalReward = array_merge($finalReward, $fixedReward);

        return $finalReward;
    }

    /** 檢查權限 */
    public function checkPermission($stageId, $user): bool
    {
        $stage = MaterialStage::where('id', $stageId)->where('is_active', 1)->first();
        if (! $stage) {
            return false;
        }

        $userStatus = UserStatus::where('uid', $user->uid)->first();
        if (! $userStatus) {
            return false;
        }

        // 檢查體力
        $stamina = $userStatus->stamina;
        if ($stamina < $stage->stamina_cost) {
            return false;
        }

        return true;
    }

    public function checkPrevStage($stageId, $uid): bool
    {
        $prevStageId = MaterialStage::where('id', $stageId)->first()->prev_stage_id;
        if (! $prevStageId) {
            return true;
        }

        $userStage = UserStage::where('uid', $uid)->where('stage_id', $prevStageId)->first();
        if (! $userStage) {
            return false;
        } else {
            return $userStage->is_clear;
        }
    }

    public function checkStageClearStatus($stageId, $uid): bool
    {
        return UserStage::where('uid', $uid)->where('stage_id', $stageId)->exists();
    }

    public function stageFormatter($stage, $uid)
    {
        return [
            'id'                => $stage->id,
            'name'              => $stage->name,
            'category_id'       => $stage->category_id,
            'map_id'            => $stage->map_id,
            'localization_name' => $stage->localization_name,
            'access_permission' => $this->checkPrevStage($stage->id, $uid),
            'description'       => $stage->description,
            'difficulty'        => $stage->difficulty,
            'stamina_cost'      => $stage->stamina_cost,
            'reward_items'      => $stage->reward_items,
            'reward_items_rate' => $stage->reward_items_rate,
            'prev_stage_id'     => $stage->prev_stage_id,
            'levelphoto_url'    => $stage->levelphoto_url,
        ];
    }

    public function updateStageStatus($stageId, $user)
    {
        $stage = MaterialStage::where('id', $stageId)->where('is_active', 1)->first();
        if (! $stage) {
            return false;
        }

        // 沒有就建立新的
        $userStage = UserStage::firstOrNew([
            'uid'      => $user->uid,
            'stage_id' => $stageId,
        ]);

        $userStage->is_clear = 1;
        $userStage->save();

        return true;
    }
}
