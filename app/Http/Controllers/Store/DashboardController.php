<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;

use App\Service\SmsService;

class DashboardController extends AppController
{
    function index(Request $request){
        $SmsService = new SmsService('vornehk_1', 'yRoQYfyo9d6', $request->store->id);
        // $result = $SmsService->send('測試簡訊_test_!@#$%[]=>987654321', '+886938691397');
// dd($result);
        return view('store.dashboard.index');
    }
}
