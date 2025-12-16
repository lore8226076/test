<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemGroup extends Model
{
    protected $table = 'item_groups';
    protected $fillable = ['item_id', 'parent_item_id', 'qty'];
    protected $hidden = ['created_at', 'updated_at'];

    // 關聯item_prices
    public function itemPrices()
    {
        return $this->belongsTo(ItemPrices::class, 'parent_item_id', 'item_id');
    }
}