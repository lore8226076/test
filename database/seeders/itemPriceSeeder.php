<?php
namespace Database\Seeders;

use App\Models\ItemPrices;
use Illuminate\Database\Seeder;

class itemPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseDatas = ItemPrices::where('tag','!=', 'Cash')->get()->toArray();
        ItemPrices::truncate();
        ItemPrices::insert(array_map(function ($d) {
            unset($d['id']);
            return $d;
        }, $baseDatas));

        // 新增商品
        $ary = $this->getProducts();

        foreach ($ary as $item) {
            try {
                $result = ItemPrices::firstOrCreate([
                    'item_id'          => $item['item_id'],
                    'tag'              => $item['tag'],
                    'currency_item_id' => $item['currency_item_id'],
                    'price'            => $item['price'],
                    'qty'              => $item['qty'],
                    'product_id'       => $item['product_id'],
                ]);

            } catch (\Exception $e) {
                \Log::error('ItemPrice 建立失敗:', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'data'    => $item,
                ]);
            }
        }
    }

    private function getProducts(): array
    {
        $baseProductIdAry = [
            'gp0001'  => 15,
            'gp0002'  => 75,
            'gp0003' => 180,
            'gp0004'  => 370,
            'gp0005'  => 950,
            'gp0006'  => 1710,
        ];

        $baseArray = [
            'item_id'          => 100,
            'tag'              => 'Cash',
            'currency_item_id' => 100,
            'price'            => 99999,
        ];

        $ary = [];
        foreach ($baseProductIdAry as $productId => $qty) {
            $ary[] = [
                'item_id'          => 100,
                'tag'              => 'Cash',
                'currency_item_id' => 100,
                'price'            => 99999,
                'qty'              => $qty,
                'product_id'       => $productId,
            ];
        }


        return $ary;
    }
}
