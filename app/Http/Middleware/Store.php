<?php
namespace App\Http\Middleware;

use Closure;
use Auth;
use Route;

use App\Service\AppService;
use App\Models\StoreMenus;
use App\Models\StoreEmployees;

class Store
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!Auth::guard('store')->check()){
            return redirect(route('store.store.login'));
        }
        \App::setLocale(session('store_locale', config('app.locale')));

        $baseBtnColor = '#1F4E5F';
        view()->share('baseBtnColor', $baseBtnColor);

        $authEmp = Auth::guard('store')->user();
        $storeEmployee = StoreEmployees::find($authEmp->id);

        $request->merge(["storeEmployee" => $storeEmployee]);
        view()->share('storeEmployee', $storeEmployee);

        $request->merge(["store" => $storeEmployee->store]);
        view()->share('store', $storeEmployee->store);

        $request->merge(["storeSetting" => $storeEmployee->store->storeSetting]);
        view()->share('storeSetting', $storeEmployee->store->storeSetting);

        $store_menu_ids = isset($storeEmployee->storeEmployeeRole)?json_decode($storeEmployee->storeEmployeeRole->store_menu_ids):[];
        view()->share('store_menu_ids', $store_menu_ids);

        $activeMenu = StoreMenus::where('link', Route::currentRouteName())->first();
        if(empty($activeMenu)){
            $activeRouteName = $this->activeMap(Route::currentRouteName());
            if($activeRouteName){
                $activeMenu = StoreMenus::where('link', $activeRouteName)->first();
            }
        }
        view()->share('activeMenu', $activeMenu);

        if($activeMenu && $activeMenu->id != 100){
            if(empty($storeEmployee->is_adm) && !in_array($activeMenu->id, $store_menu_ids)){
                session()->flash('message', ['success'=>false, 'message'=> __('您沒有權限操作此頁面')]);
                return redirect()->back();
            }
        }
        else{
            $routeNmames = explode('.', Route::currentRouteName());

            while (count($routeNmames)>0) {
                //先找一次index / list
                if(!in_array($routeNmames[count($routeNmames)-1], ['index','list'])){
                    $routeNmames[count($routeNmames)-1] = 'list';

                    $activeMenu = StoreMenus::where(['link'=>implode('.', $routeNmames)])->first();
                    if(empty($activeMenu)){
                        $routeNmames[count($routeNmames)-1] = 'list';
                        $activeMenu = StoreMenus::where(['link'=>implode('.', $routeNmames)])->first();
                    }
                    if($activeMenu){
                        view()->share('activeMenu', $activeMenu);
                        if(empty($storeEmployee->is_adm) && !in_array($activeMenu->id, $store_menu_ids)){
                            session()->flash('message', ['success'=>false, 'message'=> __('您沒有權限操作此頁面')]);
                            return redirect(route('store.dashboard.index'));
                        }
                        break;
                    }
                }
                array_pop($routeNmames);
                $activeMenu = StoreMenus::where(['link'=>implode('.', $routeNmames)])->first();

                if($activeMenu){
                    view()->share('activeMenu', $activeMenu);
                    if(empty($storeEmployee->is_adm) && !in_array($activeMenu->id, $store_menu_ids)){
                        session()->flash('message', ['success'=>false, 'message'=> __('您沒有權限操作此頁面')]);
                        return redirect(route('store.dashboard.index'));
                    }
                    break;
                }
            }
        }

        if($storeEmployee->is_adm){
            $storeMenus = StoreMenus::where('is_active',1)->orderby('sort','asc')->orderby('id','asc')->get();
        }
        else{
            $storeMenus = StoreMenus::whereIn('id',$store_menu_ids)->where('is_active',1)->orderby('sort','asc')->orderby('id','asc')->get();
        }

        view()->share('storeMenus', $storeMenus);

        return $next($request);
    }

    public function activeMap($routeName){
        $map = [
            'store.employee.edit' => 'store.employee.list',
        ];
        return isset($map[$routeName])?$map[$routeName]:false;
    }
}
