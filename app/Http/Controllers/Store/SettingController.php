<?php

namespace App\Http\Controllers\Store;

use App\Models\Admins;
use App\Models\Settings;

class SettingController extends AppController
{
    function index(){
        return view('admin.setting.index');
    }

}
