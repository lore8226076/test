<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Users;
use App\Models\UserMaps;
use App\Service\UserService;
use App\Service\UserItemService;
use App\Service\FileService;
class DashboardController extends AppController
{
    function index(Request $request){

        return view('admin.dashboard.index');
    }
}
