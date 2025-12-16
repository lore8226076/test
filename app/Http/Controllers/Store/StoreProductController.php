<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;

use Carbon\Carbon;

class StoreProductController extends AppController
{
    function vendor(){
        return view('store.product.vendor');
    }
    function category(){
        return view('store.product.category');
    }
}
