<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\StoreCustomers;

class StoreCustomerController extends AppController
{
    function category(){
        return view('store.customer.category');
    }
    function plist(){
        return view('store.customer.plist');
    }
    function pedit(Request $request, $id = null){
        return view('store.customer.pedit', compact('id'));
    }
    function clist(){
        return view('store.customer.clist');
    }
    function cedit(Request $request, $id = null){
        return view('store.customer.cedit', compact('id'));
    }
    function check(){
        return view('store.customer.check');
    }
    function update(){
        return view('store.customer.update');
    }
}
