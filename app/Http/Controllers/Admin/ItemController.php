<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Models\ItemPrices;
use App\Service\PhpOfficeService;
use App\Service\MailService;
use Carbon\Carbon;
use App\Models\GddbItems;
use App\Models\GddbLocalizationName;

class ItemController extends AppController
{
    function price(){
        return view('admin.item.price');
    }
    function price_upload(){
        return view('admin.item.price_upload');
    }

    public function rewardHelp()
    {
        $items = GddbItems::all();
        $items = $items->map(function ($item) {
            $item->zh_info = GddbLocalizationName::where('key', $item->localization_name)->first()?->zh_info ?? $item->localization_name;
            return $item;
        });
        return view('admin.reward.help', compact('items'));
    }
}
