<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CityAreaLists;
use Illuminate\Http\Request;
use Validator;
use DB;

class CityAreaController extends Controller
{
    public function __construct(Request $request) {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if($referrerDomain  != config('services.API_PASS_DOMAIN')){
            $this->middleware('auth:api', ['except' => ['index', 'list']]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $cityAreaLists = new CityAreaLists;

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
                default:
                    $cityAreaLists = $cityAreaLists->where($field, $value);
                    break;
            }
        }

        // if(empty($data['sort'])){
        //     $data['sort'] = 'sort';
        // }
        // $sortField = $data['sort'];
        // $sortDirection = !empty($data['direction'])?$data['direction']:'asc';
        // $cityAreaLists = $cityAreaLists->orderBy($sortField, $sortDirection);

        if($perPage==0){
            $cityAreaLists = $cityAreaLists->get();
        }
        else{
            $current_page = empty($data['current_page'])?1:$data['current_page'];
            $cityAreaLists = $cityAreaLists->paginate($perPage, ['*'], 'page', $current_page);
        }

        if(isset($data['to_key_value'])){
            $to_key_value = explode('-', $data['to_key_value']);
            $temp = [];
            foreach ($cityAreaLists as $cityAreaList) {
                $temp[$cityAreaList->{$to_key_value[0]}] = $cityAreaList->{$to_key_value[1]};
            }
            $cityAreaLists = $temp;
        }

        return response()->json(['data' => $cityAreaLists,], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'name' => 'required',
        ]);

        $data = $request->all();

        $message = '';

        if(empty($data['store_id'])) return response()->json(['message'=>__('店家編號錯誤'), 'field'=>'store_id', 'data'=>$data], 422);
        if(empty($data['name'])) return response()->json(['message'=>__('名稱錯誤'), 'field'=>'name'], 422);
        if(CityAreaLists::where('store_id',$data['store_id'])->where('name',$data['name'])->count())  return response()->json(['message'=>__('資料已存在'), 'field'=>'account'], 422);

        // foreach ($data as $field => $value) {
        //     switch ($field) {
        //         case 'account':
        //             if(strlen($value)<8){
        //                 return response()->json(['message'=>__('帳號錯誤，至少需要8個字元'), 'field'=>$field], 422);
        //             }
        //             break;
        //     }
        // }

        $cityAreaList = new CityAreaLists;
        $cityAreaList->fill($data);

        if($cityAreaList->save()){
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
        $cityAreaList = CityAreaLists::find($id);
        if($cityAreaList)
            return response()->json(['data' => $cityAreaList,], 200);
        else
            return response()->json(['message' => __('資料錯誤'), ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->input();

        $message = '';
        foreach ($data as $field => $value) {
        }

        $cityAreaList = CityAreaLists::find($id);

        if (!$cityAreaList) {
            return response()->json(['message' => __('資料錯誤')], 404);
        }

        $cityAreaList->fill($data);
        if($cityAreaList->save()){
            return response()->json([
                'message' => __('儲存成功'),
                /** @var object */
                'data'=>$cityAreaList
            ], 200);
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

    public function update_sort(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'sorts' => 'required|array',
            'updated_name' => 'required',
        ]);
        $data = $request->input();

        $updateData = [];
        foreach ($data['sorts'] as $i => $obj) {
            if(empty($obj['id'])) continue;
            $updateData[] = [
                'id'=>$obj['id'],
                'store_id'=>$obj['store_id'],
                'sort'=>$i+1,
                'updated_name' => $data['updated_name'],
            ];
        }
        $updateStatement = collect($updateData)->map(function ($item) {
            return 'UPDATE '. (new CityAreaLists)->getTable() .' SET sort = ' . $item['sort'] . ', updated_name = \'' . $item['updated_name'] . '\' WHERE id = ' . $item['id'];
        })->implode('; ');
        DB::unprepared($updateStatement);

        // CityAreaLists::upsert($updateData, ['id'], ['sort', 'updated_name']);

        return response()->json(['message'=>__('儲存成功'), 'data' => $updateData, ], 200);
    }

    public function list(Request $request){
        $results = [];
        $cityAreaLists = CityAreaLists::where('is_active', 1)->where('up_id', 0)->orderBy('sort')->get();
        foreach ($cityAreaLists as $cityAreaList) {

            $areas = [];
            foreach ($cityAreaList->children as $children) {
                $areas[] = [
                    'id' => $children->id,
                    'up_id' => $children->up_id,
                    'name' => $children->name,
                ];
            }
            $results[] = [
                'id' => $cityAreaList->id,
                'name' => $cityAreaList->name,
                'areas' => $areas,
            ];
        }



        return response()->json(['message'=>__('取得成功'), 'data' => $results, ], 200);
    }
}
