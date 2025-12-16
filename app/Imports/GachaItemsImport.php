<?php

namespace App\Imports;

use App\Models\GachaDetails;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class GachaItemsImport implements ToCollection
{
    private $gacha_id;

    public function __construct($gacha_id)
    {
        $this->gacha_id = $gacha_id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // 跳過第一行標題
            if ($index === 0) {
                continue;
            }

            if ($row[0] === null) {
                continue;
            }

            GachaDetails::create([
                'gacha_id' => $this->gacha_id,
                'item_id' => (int) $row[0],
                'percent' => (float) $row[1],
                'guaranteed' => $row[2] == 'SSR' ? '1' : '0',
                'qty' => (int) $row[3],
            ]);
        }
    }
}
