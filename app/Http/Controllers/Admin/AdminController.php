<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use App\Models\Admins;

class AdminController extends Controller
{
    public $result = [];
    public function __construct(){
    }

    public function list(){
        return view('admin.account.list');
    }

    public function edit(Request $request, $id = 0){
        return view('admin.account.edit', compact('id'));
    }

    public function login(Request $request){
        if($request->isMethod('post')){
            $data = $request->input();
            $attempt = [
                'account' => $data['account'],
                'password' => $data['password'],
                'is_active' => 1,
            ];

            if (Auth::guard('admin')->attempt($attempt, true)) {
                return redirect(route('admin.dashboard.index'));
            }
            else{
                session()->flash('message', ['success'=>false, 'message'=> __('帳號或密碼錯誤')]);
            }
        }
        return view('admin.admin.login');
    }

    public function logout(){

        Auth::guard('admin')->logout();
        session()->flash('message', ['success'=>true, 'message'=> __('已登出')]);

        return redirect()->route('admin.admin.login');
    }

}
