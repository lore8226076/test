<?php
namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\Models\StoreEmployeeRoles;

class StoreEmployeeRoleController extends AppController
{
    public function list (Request $request){
        return view('store.employee_role.list');
    }
    public function edit(Request $request, $id){
        if($id && !StoreEmployeeRoles::where('id',$id)->where('store_id',$request->store->id)->count()){
            session()->flash('message', ['success'=>false, 'type'=>'danger', 'message'=> __('您沒有權限或資料錯誤')]);
            return redirect()->back();
        }
        return view('store.employee_role.edit', compact('id'));
    }
}
