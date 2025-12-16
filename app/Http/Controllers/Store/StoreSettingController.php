<?php

namespace App\Http\Controllers\Store;

use App\Models\Admins;
use App\Models\Stores;

use Carbon\Carbon;

class StoreSettingController extends AppController
{
    function base(){
        return view('store.setting.base');
    }
}
