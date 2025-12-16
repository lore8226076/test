<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MapTag;

class MapTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 家園、迷宮、動作、搞怪、歡樂、治癒、情境、夢幻、布置設計、玩法實驗
        MapTag::truncate();

        $defaultTags = [
            '101' =>[
                'tag_name' => '家園',
                'localize_name' => 'ui.map.tag.home',
                'sort' => '1',
            ],
            '102' =>[
                'tag_name' => '迷宮',
                'localize_name' => 'ui.map.tag.maze',
                'sort' => '2',
            ],
            '103' =>[
                'tag_name' => '動作',
                'localize_name' => 'ui.map.tag.action',
                'sort' => '3',
            ],
            '104' =>[
                'tag_name' => '搞怪',
                'localize_name' => 'ui.map.tag.funny',
                'sort' => '4',
            ],
            '105' =>[
                'tag_name' => '歡樂',
                'localize_name' => 'ui.map.tag.happy',
                'sort' => '5',
            ],
            '106' =>[
                'tag_name' => '治癒',
                'localize_name' => 'ui.map.tag.healing',
                'sort' => '6',
            ],
            '107' =>[
                'tag_name' => '情境',
                'localize_name' => 'ui.map.tag.scenario',
                'sort' => '7',
            ],
            '108' =>[
                'tag_name' => '夢幻',
                'localize_name' => 'ui.map.tag.fantasy',
                'sort' => '8',
            ],
            '109' =>[
                'tag_name' => '布置設計',
                'localize_name' => 'ui.map.tag.design',
                'sort' => '9',
            ],
            '110' =>[
                'tag_name' => '玩法實驗',
                'localize_name' => 'ui.map.tag.experiment',
                'sort' => '10',
            ],
        ];

        foreach ($defaultTags as $key => $value) {
            MapTag::insert([
                'id' => $key,
                'tag_name' => $value['tag_name'],
                'localize_name' => $value['localize_name'],
                'sort' => $value['sort'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return;
    }
}
