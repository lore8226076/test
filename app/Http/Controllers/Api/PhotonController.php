<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Models\UserEquipments;
use App\Service\ErrorService;

use Illuminate\Http\Request;
use Validator;
use DB;

class PhotonController extends Controller
{
    private static $photon_key = null;

    public static function getAuthKey()
    {
        if (is_null(self::$photon_key)) {
            self::$photon_key = config('services.photon.key');
        }
        return self::$photon_key;
    }

    public function __construct(Request $request) {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if($referrerDomain  != config('services.API_PASS_DOMAIN')){
            $this->middleware('auth:api', ['except' => ['auth', 'userProfile'] ]);
        }
    }
    // private static $photon_key = '22e0be59-e03c-49c2-b863-ba285b0f2e25';
    // private static $photon_key = 'bbb94e16-cace-43d4-be39-d6d2a719c1b7';

    public static function check_photon($data){
        self::getAuthKey();

        if(empty($data['photon_key'])) return response()->json(['ResultCode' => 2, "Message" => "photon key failed." ], 200);
        if($data['photon_key'] != self::$photon_key) return response()->json(['ResultCode' => 3, "Message" => "photon key failed." ], 200);
        return true;
    }

    public function auth(Request $request)
    {
        $data = $request->input();
// \Log::debug("[" . __METHOD__ . "] " . json_encode($data));

        $check = self::check_photon($data);
// \Log::debug("[" . __METHOD__ . "] " . json_encode($check));
        if($check !== true) return $check;

        if(empty($data['uid'])) return response()->json(['ResultCode' => 1001, "Message" => "uid failed." ], 200);
        if(empty(Users::where('uid', $data['uid'])->count())) return response()->json(['ResultCode' => 1002, "Message" => "user failed." ], 200);

        return response()->json(['ResultCode' => 1, "UserId" => $data['uid'], "Data"=>["uid" => $data['uid']] ], 200);
    }

    public function userProfile($uid) {

        if(empty($uid)) return response()->json(ErrorService::errorCode('Photon:userProfile', 'AUTH:0005'), 422);

        $user = Users::with('userEquipment')->where('uid', $uid)->first();

        if(empty($user)) return response()->json(ErrorService::errorCode('Photon:userProfile', 'AUTH:0006'), 422);

        return response()->json(['data' => $user ],200);
    }
}
