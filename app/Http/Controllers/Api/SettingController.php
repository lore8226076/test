<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Service\ErrorService;

use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(Request $request)
    {
        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['showByName']]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function showByName(Request $request, $name)
    {
        $data = $request->input();
        $lang = $data['lang'] ?? 'zh';
        $lang = config('language.' . $lang);
        $setting = Settings::where('name', $name)->first();
        if(empty($setting)){
            $setting = new Settings;
            $setting->name = $name;
            switch ($name) {
                case 'avatar_to_ticket':
                    $setting->value = json_encode(['SSR'=>30, 'SR'=>7, 'R'=>1, 'N'=>1]);
                case 'login_msg_board':
                    $setting->value = "歡迎來到 《鏘鏘鏘-創意派對》 刪檔封測。\n\n 誠摯邀請您加入Discord、官方Line@ 獲取最新消息以及回報問題。";
                    $setting->en_value = 'Welcome to "Clang Party".※ Please use quick login to play directly.
※ Participation rewards will be sent as serial numbers to your phone during the official launch, so please remember to log in on the reservation website with your phone.
We sincerely invite you to join our Discord and official Line@ to get the latest updates and report any issues.';
                case 'maintenance_msg':
                        $setting->value = "目前正在進行維護，請稍後再試。";
                        $setting->en_value = 'Currently undergoing maintenance, please try again later.';
                    break;
            }
            $setting->save();
        }

        switch ($name) {
            case 'avatar_to_ticket':
                $setting->value = json_decode($setting->value, true);
                break;
            case 'login_msg_board':
                $setting->value = $lang == 'zh' ? $setting->value : $setting->en_value;
                break;
            case 'maintenance_msg':
                $setting->value = $lang == 'zh' ? $setting->value : $setting->en_value;
                break;
        }

        return response()->json([
            'data'        => $setting,
        ], 200);
    }

    public function store(Request $request)
    {

        $data = $request->input();

        $message = '';

        if(empty($data['name'])) return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 422);
        if(empty($data['value'])) return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 422);

        $setting = new Settings;
        $setting->fill($data);

        switch ($setting->name) {
            case 'avatar_to_ticket':
                $setting->value = json_encode($data['value']);
                break;
        }

        if($setting->save()){
            return response()->json(['data'=>$setting], 200);
        }
        else{
             return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0003'), 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $data = $request->input();

        $message = '';
        foreach ($data as $field => $value) {
        }

        $setting = Settings::find($id);

        if (!$setting) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0002'), 404);
        }

        $setting->fill($data);

        switch ($setting->name) {
            case 'avatar_to_ticket':
                $setting->value = json_encode($data['value']);
                break;
        }

        if($setting->save()){
            return response()->json(['data'=>$setting], 200);
        }
        else{
            return response()->json(ErrorService::errorCode(__METHOD__, 'SYSTEM:0003'), 500);
        }
    }

}
