<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreEmployees;
use App\Models\Users;
use App\Models\UserMaps;

use App\Service\ErrorService;

use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{
    public function __construct(Request $request) {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        $path = $request->path();
        if (str_starts_with($path, 'api/')) {
            if($referrerDomain  != config('services.API_PASS_DOMAIN')){
                $this->middleware('auth:api', ['except' => [] ]);
            }
        } else if (str_starts_with($path, 'app/')) {
            $this->middleware('auth:app', ['except' => [] ]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $users = Users::with('userMaps');

        $data = $request->input();

        foreach ($data as $field => $value) {
            $field = str_replace('__', '.', $field);
            switch ($field) {
                case 'per_page':
                case 'to_key_value':
                case 'current_page':
                case 'sort':
                case 'direction':
                case '_token':
                    break;
                case 'uid':
                case 'account':
                case 'name':
                    if(!empty($value)){
                        $users = $users->whereRaw('LOWER(' . $field . ') like ?', ['%' . strtolower($value) . '%']);
                    }
                    break;
                default:
                    $users = $users->where($field, $value);
                    break;
            }
        }

        if(empty($data['sort'])){
            $data['sort'] = (new Users)->getTable().'.sort';
        }
        $sortField = $data['sort'];
        $sortDirection = !empty($data['direction'])?$data['direction']:'asc';
        $users = $users->orderBy($sortField, $sortDirection);

        if($perPage==0){
            $users = $users->get();
        }
        else{
            $current_page = empty($data['current_page'])?1:$data['current_page'];
            $users = $users->paginate($perPage, ['*'], 'page', $current_page);
        }

        if(isset($data['to_key_value'])){
            $users = $users->get();
            $to_key_value = explode('-', $data['to_key_value']);
            $temp = [];
            foreach ($users as $user) {
                $temp[$user->{$to_key_value[0]}] = $user->{$to_key_value[1]};
            }
            $users = $temp;
        }

        return response()->json([
            'data' => $users,
            'dd' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'name' => 'required',
            'store_menu_ids' => 'required',
        ]);

        $data = $request->all();

        if(empty($data['store_id'])) return response()->json(['message'=>__('店家編號錯誤'), 'field'=>'store_id', 'data'=>$data], 422);
        if(empty($data['name'])) return response()->json(['message'=>__('名稱錯誤'), 'field'=>'name', 'data'=>$data], 422);
        if(Users::where('store_id',$data['store_id'])->where('name',$data['name'])->count())  return response()->json(['message'=>__('名稱重複'), 'field'=>'name'], 422);

        // foreach ($data as $field => $value) {
        //     switch ($field) {
        //         case 'store_employee_role_id':
        //             if(empty($value)){
        //                 return response()->json(['message'=>__('身分權限錯誤'), 'field'=>$field], 422);
        //             }
        //             break;
        //     }
        // }

        $user = new Users;
        $user->fill($data);

        if($user->save()){
            return response()->json(['message' => __('儲存成功'), 'data'=>$data], 200);
        }
        else{
            return response()->json(['message' => __('儲存失敗'), 'data'=>$data], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json([
            'data' => Users::where('id', $id)->first()?:new Users,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->input();
// return response()->json(['data'=>$data, 'message' => __('資料錯誤')], 200);
        // $validator = Validator::make($data, [
        //     'account' => 'required',
        // ]);
        // if ($validator->fails()) {
        //     return response()->json($validator->errors(), 422);
        // }
        // if(Users::where('store_id',$data['store_id'])->where('id','!=',$id)->where('name',$data['name'])->count())  return response()->json(['message'=>__('名稱重複'), 'field'=>'name'], 422);

        foreach ($data as $field => $value) {
            switch ($field) {
                case 'account':
                    if(strlen($value)<4){
                        return response()->json(ErrorService::errorCode('user:update', 'AUTH:0001'), 422);
                    }
                    if(Users::where('id','!=',$id)->where('account',$data['account'])->count()) return response()->json(ErrorService::errorCode('user:update', 'AUTH:0004'), 422);
                    break;
                case 'password':
                    if(strlen($value)<4){
                        return response()->json(ErrorService::errorCode('user:update', 'AUTH:0002'), 422);
                    }
                    break;
                // case 'email':
                //     if(!empty($value) && filter_var($value, FILTER_VALIDATE_EMAIL) === false){
                //         return response()->json(['message'=>__('Email格式錯誤'), 'field'=>$field], 422);
                //     }
                //     break;
                // case 'store_employee_role_id':
                //     if(empty($value)){
                //         return response()->json(['message'=>__('身分權限錯誤'), 'field'=>$field], 422);
                //     }
                //     break;
            }
        }

        $user = Users::find($id);

        if (!$user) {
            return response()->json(['message' => __('資料錯誤')], 404);
        }

        $user->fill($data);
        if(!empty($data['account'])){
            $user->account = $data['account'];
        }
        if(!empty($data['password'])){
            $user->password = $data['password'];
        }
        if($user->save()){
            return response()->json(['message' => __('儲存成功'), 'data'=>$data], 200);
        }
        else{
            return response()->json(['message' => __('儲存失敗')], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function delete_maps($user_id){
        UserMaps::where('user_id',$user_id)->delete();
        return response()->json(['message' => __('刪除成功'), 'data'=>[] ], 200);
    }

    public function change_g8pad(){
        $g8 = UserMaps::find(115);

        $ids = [85,94,98];
        foreach ($ids as $user_id) {
            $userMap = UserMaps::where('user_id',$user_id)->first();
            $userMap->map_data = $g8->map_data;
            $userMap->save();
        }
        return response()->json(['message' => __('更新成功'), 'data'=>[] ], 200);
    }

}
