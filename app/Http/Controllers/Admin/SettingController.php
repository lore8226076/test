<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admins;

class SettingController extends AppController
{
    function index(){
        return view('admin.setting.index');
    }

}
