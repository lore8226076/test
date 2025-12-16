<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\AdminRoles;
use App\Models\AdminMenus;

class AdminRoleController extends AppController
{
    public function list (Request $request){
        return view('admin.account.role.list');
    }
    public function edit(Request $request, $id){
        if($id && !AdminRoles::where('id',$id)->count()){
            session()->flash('message', ['success'=>false, 'type'=>'danger', 'message'=> __('您沒有權限或資料錯誤')]);
            return redirect()->back();
        }

        $allAdminMenus = AdminMenus::where('is_active',1)->orderby('sort','asc')->orderby('id','asc')->get();

        return view('admin.account.role.edit', compact('id', 'allAdminMenus'));
    }
}
