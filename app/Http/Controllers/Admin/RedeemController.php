<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RedeemCode;

class RedeemController extends Controller
{
    public function index() 
    {
        return view('admin.redeem.list');
    }
}
