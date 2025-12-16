<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;

use Carbon\Carbon;

class StoreServiceController extends AppController
{
    function category(){
        return view('store.service.category');
    }
    function list(){
        return view('store.service.list');
    }
    function edit(Request $request, $id = null){
        return view('store.service.edit', compact('id'));
    }
}
