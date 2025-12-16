<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\MaterialStage;
use Illuminate\Support\Facades\DB;

class MaterialStageImport implements ToCollection, WithStartRow
{
    public function collection(Collection $collection)
    {
        foreach ($collection as $index => $row) {
            try {
                DB::table('material_stages')->insert([
                    'id' => $row[0],
                    'name' => $row[1],
                    'category_id' => $row[2],
                    'map_id' => $row[3],
                    'localization_name' => $row[4],
                    'description' => $row[5],
                    'image_path' => $row[6],
                    'difficulty' => $row[7],
                    'stamina_cost' => $row[8],
                    'random_reward_items_rate' => $row[9],
                    'random_reward_count' => $row[10],
                    'random_reward' => $row[11],
                    'fixed_reward' => $row[12],
                    'prev_stage_id' => $row[13],
                    'player_level' => $row[14],
                ]);
            } catch (\Throwable $e) {
                throw $e;
            }
        }
    }

    // 從第2行開始
    public function startRow(): int
    {
        return 2;
    }
}
