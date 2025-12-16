<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\Models\StoreEmployees;

class StoreEmployeeController extends AppController
{
    function list(){
        return view('store.employee.list');
    }

    function edit(Request $request, $id = 0){
        if($id && !StoreEmployees::where('id',$id)->where('store_id',$request->store->id)->count()){
            session()->flash('message', ['success'=>false, 'type'=>'danger', 'message'=> __('您沒有權限或資料錯誤')]);
            return redirect()->back();
        }
        return view('store.employee.edit', compact('id'));
    }

    function schedule($id = 0){
        if($id && !StoreEmployees::where('id',$id)->where('store_id',$request->store->id)->count()){
            session()->flash('message', ['success'=>false, 'type'=>'danger', 'message'=> __('您沒有權限或資料錯誤')]);
            return redirect()->back();
        }
        return view('store.employee.schedule', compact('id'));
    }

    function service($id = 0){
        if($id && !StoreEmployees::where('id',$id)->where('store_id',$request->store->id)->count()){
            session()->flash('message', ['success'=>false, 'type'=>'danger', 'message'=> __('您沒有權限或資料錯誤')]);
            return redirect()->back();
        }
        return view('store.employee.service', compact('id'));
    }

    function booking($id = 0){
        if($id && !StoreEmployees::where('id',$id)->where('store_id',$request->store->id)->count()){
            session()->flash('message', ['success'=>false, 'type'=>'danger', 'message'=> __('您沒有權限或資料錯誤')]);
            return redirect()->back();
        }
        return view('store.employee.booking', compact('id'));
    }
}
