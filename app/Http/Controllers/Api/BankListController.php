<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankLists;
use Illuminate\Http\Request;
use Validator;
use DB;

class BankListController extends Controller
{
    public function __construct(Request $request) {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if($referrerDomain  != config('services.API_PASS_DOMAIN')){
            $this->middleware('auth:api', ['except' => ['index']]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $bankLists = new BankLists;

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
                    $bankLists = $bankLists->where($field, $value);
                    break;
            }
        }

        // if(empty($data['sort'])){
        //     $data['sort'] = 'sort';
        // }
        // $sortField = $data['sort'];
        // $sortDirection = !empty($data['direction'])?$data['direction']:'asc';
        // $bankLists = $bankLists->orderBy($sortField, $sortDirection);

        if($perPage==0){
            $bankLists = $bankLists->get();
        }
        else{
            $current_page = empty($data['current_page'])?1:$data['current_page'];
            $bankLists = $bankLists->paginate($perPage, ['*'], 'page', $current_page);
        }

        if(isset($data['to_key_value'])){
            $to_key_value = explode('-', $data['to_key_value']);
            $temp = [];
            foreach ($bankLists as $bankList) {
                $temp[$bankList->{$to_key_value[0]}] = $bankList->{$to_key_value[1]};
            }
            $bankLists = $temp;
        }

        return response()->json(['data' => $bankLists,], 200);
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
        if(BankLists::where('store_id',$data['store_id'])->where('name',$data['name'])->count())  return response()->json(['message'=>__('資料已存在'), 'field'=>'account'], 422);

        // foreach ($data as $field => $value) {
        //     switch ($field) {
        //         case 'account':
        //             if(strlen($value)<8){
        //                 return response()->json(['message'=>__('帳號錯誤，至少需要8個字元'), 'field'=>$field], 422);
        //             }
        //             break;
        //     }
        // }

        $bankList = new BankLists;
        $bankList->fill($data);

        if($bankList->save()){
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
        $bankList = BankLists::find($id);
        if($bankList)
            return response()->json(['data' => $bankList,], 200);
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

        $bankList = BankLists::find($id);

        if (!$bankList) {
            return response()->json(['message' => __('資料錯誤')], 404);
        }

        $bankList->fill($data);
        if($bankList->save()){
            return response()->json([
                'message' => __('儲存成功'),
                /** @var object */
                'data'=>$bankList
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
            return 'UPDATE '. (new BankLists)->getTable() .' SET sort = ' . $item['sort'] . ', updated_name = \'' . $item['updated_name'] . '\' WHERE id = ' . $item['id'];
        })->implode('; ');
        DB::unprepared($updateStatement);

        // BankLists::upsert($updateData, ['id'], ['sort', 'updated_name']);

        return response()->json(['message'=>__('儲存成功'), 'data' => $updateData, ], 200);
    }
}
