<?php
namespace App\Http\Middleware;

use Closure;
use Auth;
use Route;

use App\Models\AdminMenus;
use App\Models\Admins;
use App\Models\AdminPermissions;

class Admin
{
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!Auth::guard('admin')->check()){
            return redirect(route('admin.admin.login'));
        }
        \App::setLocale(session('store_locale', config('app.locale')));

        $baseBtnColor = '#1F4E5F';
        view()->share('baseBtnColor', $baseBtnColor);

        $authEmp = Auth::guard('admin')->user();
        $admin = Admins::find($authEmp->id);

        $request->merge(["admin" => $admin]);
        view()->share('admin', $admin);

        $admin_menu_ids = [];
        if(isset($admin->adminRole) && $admin->adminRole->is_adm){
            $admin_menu_ids = AdminMenus::all()->pluck('id')->toArray();
        }
        else{
            $admin_menu_ids = isset($admin->adminRole)?json_decode($admin->adminRole->admin_menu_ids):[];
        }
        view()->share('admin_menu_ids', $admin_menu_ids);

        $activeMenu = AdminMenus::where('link', Route::currentRouteName())->first();
        if(empty($activeMenu)){
            $activeRouteName = $this->activeMap(Route::currentRouteName());
            if($activeRouteName){
                $activeMenu = AdminMenus::where('link', $activeRouteName)->first();
            }
        }
        view()->share('activeMenu', $activeMenu);

        if($activeMenu && $activeMenu->id != 100){
            if(empty($admin->is_adm) && !in_array($activeMenu->id, $admin_menu_ids)){
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

                    $activeMenu = AdminMenus::where(['link'=>implode('.', $routeNmames)])->first();
                    if(empty($activeMenu)){
                        $routeNmames[count($routeNmames)-1] = 'list';
                        $activeMenu = AdminMenus::where(['link'=>implode('.', $routeNmames)])->first();
                    }
                    if($activeMenu){
                        view()->share('activeMenu', $activeMenu);
                        if(empty($admin->is_adm) && !in_array($activeMenu->id, $admin_menu_ids)){
                            session()->flash('message', ['success'=>false, 'message'=> __('您沒有權限操作此頁面')]);
                            return redirect(route('store.dashboard.index'));
                        }
                        break;
                    }
                }
                array_pop($routeNmames);
                $activeMenu = AdminMenus::where(['link'=>implode('.', $routeNmames)])->first();

                if($activeMenu){
                    view()->share('activeMenu', $activeMenu);
                    if(empty($admin->is_adm) && !in_array($activeMenu->id, $admin_menu_ids)){
                        session()->flash('message', ['success'=>false, 'message'=> __('您沒有權限操作此頁面')]);
                        return redirect(route('store.dashboard.index'));
                    }
                    break;
                }
            }
        }

        if($admin->is_adm){
            $adminMenus = AdminMenus::where('is_active',1)->orderby('sort','asc')->orderby('id','asc')->get();
        }
        else{
            $adminMenus = AdminMenus::whereIn('id',$admin_menu_ids)->where('is_active',1)->orderby('sort','asc')->orderby('id','asc')->get();
        }

        view()->share('adminMenus', $adminMenus);

        return $next($request);
    }

    public function activeMap($routeName){
        $map = [
            // 'admin.admin.edit' => 'admin.admin.list',
        ];
        return isset($map[$routeName])?$map[$routeName]:false;
    }
}
