<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use App\Models\Stores;

class StoreController extends Controller
{
    public $result = [];
    public function __construct(){
    }

    public function index(){
        // return view('store.store.index');
    }

    public function login(Request $request){
        if($request->isMethod('post')){
            $data = $request->input();
            $attempt = [
                'account' => $data['account'],
                'password' => $data['password'],
                'is_active' => 1,
            ];
            if (Auth::guard('store')->attempt($attempt, true)) {
                return redirect(route('store.dashboard.index'));
            }
            else{
                session()->flash('message', ['success'=>false, 'message'=> __('帳號或密碼錯誤')]);
            }
        }
        return view('store.store.login');
    }

    public function employee_login(Request $request, $store_id = false){
        $store_id = base64_decode($store_id);
        $store = Stores::find($store_id);

        if(empty($store)) return redirect()->to(config('services.APP_URL'));

        if($request->isMethod('post')){
            $data = $request->input();
            $attempt = [
                'store_id' => $store->id,
                'account' => $data['account'],
                'password' => $data['password'],
                'is_active' => 1,
            ];
            if (Auth::guard('store')->attempt($attempt, true)) {
                return redirect(route('store.dashboard.index'));
            }
            else{
                session()->flash('message', ['success'=>false, 'message'=> __('帳號或密碼錯誤')]);
            }
        }
        $this->result['store'] = $store;
        return view('store.store.employee_login', $this->result);
    }

    public function logout(){
        // $url = route('store.store.login');

        Auth::guard('store')->logout();
        session()->flash('message', ['success'=>true, 'message'=> __('已登出')]);

        return redirect()->route('store.store.login');
    }
}
