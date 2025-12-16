<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class WebviewController extends Controller
{
    function activity(){
    	return view('webview.activity');
    }
}
