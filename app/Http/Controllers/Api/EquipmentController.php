<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Models\UserEquipments;
use App\Service\ErrorService;

use Illuminate\Http\Request;
use Validator;
use DB;

class EquipmentController extends Controller
{
    public function __construct(Request $request) {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if($referrerDomain  != config('services.API_PASS_DOMAIN')){
            $this->middleware('auth:api', ['except' => ['oneByUid'] ]);
        }
    }

    public function update(Request $request)
    {
        $data = $request->input();

        $userEquipment = self::getInitEquipment(auth()->guard('api')->user()->id);

        $userEquipment->fill($data);
        $userEquipment->save();

        return response()->json(['data' => ['userEquipment'=>$userEquipment], ], 200);
    }

    public function one(Request $request)
    {
        $data = $request->input();

        $userEquipment = self::getInitEquipment(auth()->guard('api')->user()->id);

        return response()->json(['data' => ['userEquipment'=>$userEquipment], ], 200);
    }

    public function oneByUid(Request $request, $uid)
    {
        $data = $request->input();

        if(empty($uid)) return response()->json(ErrorService::errorCode('equipment:oneByUid', 'AUTH:0005'), 422);

        $user = Users::where('uid', $uid)->first();

        if(empty($user)) return response()->json(ErrorService::errorCode('equipment:oneByUid', 'AUTH:0006'), 422);

        $userEquipment = self::getInitEquipment($user->id);

        return response()->json(['data' => ['userEquipment'=>$userEquipment], ], 200);
    }

    public static function getInitEquipment($user_id){
        $userEquipment = UserEquipments::where('user_id', $user_id)->first();

        if(empty($userEquipment)){
            $userEquipment = new UserEquipments;
            $userEquipment->user_id = $user_id;
            $userEquipment->save();

            $userEquipment = UserEquipments::where('user_id', $user_id)->first();
        }
        return $userEquipment;
    }

}
