<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Models\Admins;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
class AdminsController extends Controller
{
    public function __construct(Request $request) {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if($referrerDomain  != config('services.API_PASS_DOMAIN')){
            $this->middleware('auth:api', ['except' => ['apiLog']]);
        }
    }
    public function index()
    {
        $admins = Admins::get();

        if(isset($admins) && count($admins) > 0){
            $data = ['admins' => $admins];
            return $this->makeJson(1,$data,null);
        }else{
            return $this->makeJson(0,null,__('找不到任何管理者帳號'));
        }

    }

    public function show(Request $request,$id)
    {
        $admin = Admins::find($id);

        if(isset($admin)){
            $data = ['admin' => $admin];
            return $this->makeJson(1,$data,null);
        }else{
            return $this->makeJson(0,null,__('找不到該管理者帳號'));
        }

    }

    public function store(Request $request)
    {
        $input = ['title' => $request->title , 'content' => $request->content];

        $admin = Admins::create($input);

        if(isset($admin)){
            $data = ['admin' => $admin];
            return $this->makeJson(1,$data,__('新增管理者帳號成功'));
        }else{
            $data = ['admin' => $admin];
            return $this->makeJson(0,null,__('新增管理者帳號失敗'));
        }

    }

    public function update(Request $request,$id)
    {

        try {
            $admin = Admins::findOrFail($id);
            $admin->title = $request->title;
            $admin->content = $request->content;
            $admin->save();
        } catch (Throwable $e) {
            \Log::error("更新管理者帳號失敗: {$e->getMessage()}");
            //更新失敗
            $data = ['admin' => $admin];
            return $this->makeJson(0,null,__('更新管理者帳號失敗'));
        }

        $data = ['admin' => $admin];
        return $this->makeJson(1,$data,__('更新管理者帳號成功'));
    }

    public function destroy($id)
    {
        try {
            $admin = Admins::findOrFail($id);
            $admin->delete();
        } catch (Throwable $e) {
            \Log::error("刪除管理者帳號失敗: {$e->getMessage()}");
            //刪除失敗
            return $this->makeJson(0,null,__('刪除管理者帳號失敗'));
        }
        return $this->makeJson(1,null,__('刪除管理者帳號成功'));
    }

    // 刷新gddata item的資料
    public function refreshGddataItemsData()
    {
        try {
            Artisan::call('app:import-items');
            Artisan::call('surgame:init');
            return $this->makeJson(0,null,__('資料刷新成功！'));
        } catch (\Throwable $e) {
            \Log::error("資料刷新失敗: {$e->getMessage()}");
            return $this->makeJson(0,null,__('資料刷新失敗！'));
        }
    }

    // 重置指定玩家surgame資料（僅測試環境）
    public function resetUserSurgameData(Request $request)
    {
        // 限制只能在測試環境使用
        $allowedDomains = [
            'clang-party-dev.wow-dragon.com.tw',
            'clang-party_dev.wow-dragon.com.tw',
            'clang-party-qa.wow-dragon.com.tw',
            'clang_party_qa.wow-dragon.com.tw',
            'laravelapi.test'
        ];

        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);

        if (!in_array($referrerDomain, $allowedDomains)) {
            \Log::warning('非測試環境嘗試重置 surgame 資料', [
                'domain' => $referrerDomain,
                'ip' => $request->ip()
            ]);
            return $this->makeJson(0, null, __('此功能僅限測試環境使用'));
        }

        $uid = $request->input('uid');
        if(empty($uid)){
            return $this->makeJson(0, null, __('請提供uid參數'));
        }

        try {
            // 檢查用戶是否存在
            $user = \App\Models\Users::where('uid', $uid)->first();
            if (!$user) {
                return $this->makeJson(0, null, __('找不到該用戶'));
            }

            // 執行指令，使用 --no-interaction 跳過確認
            Artisan::call('app:init-user-surgame-infomation', [
                'uid' => $uid,
                '--no-interaction' => true,
            ]);

            $output = Artisan::output();

            \Log::info('重置用戶surgame資料成功', [
                'uid' => $uid,
                'user_id' => $user->id,
                'domain' => $referrerDomain,
                'output' => $output
            ]);

            return $this->makeJson(1, [
                'uid' => $uid,
                'output' => $output
            ], __('重置用戶surgame資料成功！'));

        } catch (\Throwable $e) {
            \Log::error("重置用戶surgame資料失敗: {$e->getMessage()}");
            return $this->makeJson(0, null, __('重置失敗！'));
        }
    }

    // API紀錄
    public function apiLog(Request $request)
    {
        Log::channel('game_server_log')->info('遊戲連線數據紀錄：', $request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'API紀錄成功',
        ]);
    }
}
