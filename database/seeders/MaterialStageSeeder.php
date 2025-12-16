<?php
namespace Database\Seeders;

use App\Models\GddbItems;
use App\Models\MaterialStage;
use App\Models\MaterialStageCategory;
use Illuminate\Database\Seeder;

class MaterialStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MaterialStage::truncate();
        MaterialStageCategory::truncate();

        $parentCategory = $this->createMaterialStageCategory();
        $category       = $this->createMaterialStageCategoryChild($parentCategory->id);
        $this->createMaterialStage($category->id, 4);
    }

    private function createMaterialStageCategory()
    {
        return MaterialStageCategory::create([
            'name'              => '關卡',
            'localization_name' => '關卡',
        ]);
    }
    private function createMaterialStageCategoryChild($parentId)
    {
        return MaterialStageCategory::create([
            'name'              => '子關卡1',
            'localization_name' => 'ui.localzation.material.stage.1',
            'parent_id'         => $parentId,
            'sort'              => 0,
            'is_active'         => 1,
            'start_time'        => null,
            'end_time'          => null,
        ]);

    }

    private function createMaterialStage($categoryId, $createCount = 1)
    {
        if ($createCount > 0) {
            // 掉落物
            for ($i = 0; $i < $createCount; $i++) {
                $rewardItems = [
                    ['item_id' => 3020022, 'amount' => 10 * (10 ** $i)],
                    ['item_id' => 3020018, 'amount' => 1],
                    ['item_id' => 3020016, 'amount' => 1],
                    ['item_id' => 3020021, 'amount' => 1],
                ];

                if ($i == 0) {
                    $rewardItems = array_slice($rewardItems, 0, 1);
                } elseif ($i == 1) {
                    $rewardItems = array_slice($rewardItems, 0, 2);
                }
                // 插入101道具，數量隨難度變
                $item101Amount = $i === 0 ? 100 : 1000;
                array_unshift($rewardItems, ['item_id' => 101, 'amount' => $item101Amount]);
                // 如果是最後一筆，再加入頂級獎勵 8100180 1個
                if ($i == 2) {
                    $rewardItems[] = ['item_id' => 3030020, 'amount' => 1];
                }

                $rewardItemsRate = $this->calculateRewardItemsRate($rewardItems);

                $stage = MaterialStage::create([
                    'name'              => '關卡1 難度' . ($i + 1),
                    'localization_name' => 'localization.material.stage.1.' . ($i + 1),
                    'description'       => '關卡1 難度' . ($i + 1) . "的描述。 掉落物品：" . $i + 1,
                    'map_id'            => 10,
                    'category_id'       => $categoryId,
                    'difficulty'        => $i + 1,
                    'sort'              => 0,
                    'is_active'         => $i == 3 ? 0 : 1,
                    'stamina_cost'      => ($i + 1) * 10,
                    'reward_items'      => $rewardItems,
                    'reward_items_rate' => $rewardItemsRate,
                    'prev_stage_id'     => $i == 0 ? null : $i ,
                ]);
            }
        }
    }

    // 計算掉落機率
    private function calculateRewardItemsRate($rewardItems)
    {
        $rewardItemsRate = [];
        // 只統計 R、SR、SSR
        $allowRarity = ['R', 'S', 'SR', 'SSR'];
        foreach ($rewardItems as $item) {
            $dbItem = GddbItems::where('item_id', $item['item_id'])->first();
            if ($dbItem && in_array($dbItem->rarity, $allowRarity)) {
                $rewardItemsRate[] = [
                    'item_id' => $dbItem->item_id,
                    'rarity'  => $dbItem->rarity,
                ];
            }
        }

        $total = count($rewardItemsRate);
        if ($total === 0) {
            return [];
        }

        $rarityCount = [];
        foreach ($rewardItemsRate as $item) {
            $rarity = $item['rarity'];
            if (!isset($rarityCount[$rarity])) {
                $rarityCount[$rarity] = 0;
            }
            $rarityCount[$rarity]++;
        }

        // 計算機率
        $rarityProbability = [];
        foreach ($rarityCount as $rarity => $count) {
            $rarityProbability[$rarity] = round(($count / $total) * 100, 2);
        }

        // 指定排序
        $rarityOrder       = ['R', 'SR', 'SSR'];
        $sortedProbability = [];
        foreach ($rarityOrder as $r) {
            if (isset($rarityProbability[$r])) {
                $sortedProbability[$r] = $rarityProbability[$r];
            }
        }

        return $sortedProbability;
    }


}
