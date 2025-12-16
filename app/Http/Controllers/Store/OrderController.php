<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;

use Carbon\Carbon;

class OrderController extends AppController
{
    function edit(Request $request, $id = 0){
        return view('store.order.edit', compact('id'));
    }
    function list(Request $request){
        return view('store.order.list');
    }
    function catch(Request $request){
        return view('store.order.catch');
    }
    function finish(Request $request){
        return view('store.order.finish');
    }
    function waitpay(Request $request){
        return view('store.order.waitpay');
    }
    function close(Request $request){
        return view('store.order.close');
    }
    function check_update(Request $request){
        return view('store.order.check_update');
    }
}
