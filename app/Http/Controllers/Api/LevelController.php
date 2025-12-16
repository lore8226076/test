<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Models\UserMaps;
use App\Models\LevelSettings;
use App\Service\ErrorService;
use App\Service\UserService;

use Illuminate\Http\Request;
use Validator;
use DB;

class LevelController extends Controller
{
    public function __construct(Request $request) {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if($referrerDomain  != config('services.API_PASS_DOMAIN')){
            $this->middleware('auth:api', ['except' => ['get_level', 'one'] ]);
        }
    }

    public function get_level(Request $request, $level, $sub_level, $section)
    {
        $data = $request->input();

        // $user = Users::find(auth()->guard('api')->user()->id);

        $levelSetting = LevelSettings::with('user_map')->where('level', $level)->where('sub_level', $sub_level)->where('section', $section)->first();

        if(empty($levelSetting)) return response()->json(ErrorService::errorCode('level:one', 'SYSTEM:0002'), 401);

        // if($userMap->user_id != $userMap->user->id) return response()->json(ErrorService::errorCode('map:one', 'SYSTEM:0001'), 401);

        return response()->json(['data' => ['levelSetting'=>$levelSetting], ], 200);
    }

    public function one(Request $request, $id)
    {
        $data = $request->input();

        // $user = Users::find(auth()->guard('api')->user()->id);

        $levelSetting = LevelSettings::with('user_map')->find($id);

        if(empty($levelSetting)) return response()->json(ErrorService::errorCode('level:one', 'SYSTEM:0002'), 401);

        // if($userMap->user_id != $userMap->user->id) return response()->json(ErrorService::errorCode('map:one', 'SYSTEM:0001'), 401);

        return response()->json(['data' => ['levelSetting'=>$levelSetting], ], 200);
    }
}
