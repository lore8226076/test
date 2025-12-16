<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Models\UserItems;
use App\Models\ItemPriceUploads;
use App\Models\ItemPriceUploadDetails;
use App\Models\ItemPrices;

use App\Service\ErrorService;
use App\Service\UserItemService;
use App\Service\UserMallOrderService;
use App\Service\UserService;
use App\Service\FileService;
use App\Service\PhpOfficeService;

use Validator;
use DB;
use Carbon\Carbon;

class ItemPriceUploadController extends Controller
{
    public function __construct(Request $request) {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if($referrerDomain  != config('services.API_PASS_DOMAIN')){
            $this->middleware('auth:api', ['except' => []]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 20);

        $itemPriceUploads = ItemPriceUploads::with('itemPriceUploadDetails');

        $data = $request->input();
        foreach ($data as $field => $value) {
            $field = str_replace('__', '.', $field);
            switch ($field) {
                case 'per_page':
                case 'current_page':
                case 'to_key_value':
                case 'sort':
                case 'direction':
                case 'getCount':
                case '_token':
                    break;
                case 'id':
                    if(!empty($value)){
                        $itemPriceUploads = $itemPriceUploads->where($field, $value);
                    }
                    break;
                case 'status':
                    $itemPriceUploads = $itemPriceUploads->whereIn($field, explode('_', $value));
                    break;
                case 'pay_month':
                    if(!empty($value)){
                        $pay_start = $value."-01";
                        $pay_end = date('Y-m-t', strtotime($pay_start));
                        $itemPriceUploads = $itemPriceUploads->whereBetween('pay_date', [$pay_start, $pay_end]);
                    }
                    break;
                default:
                    $itemPriceUploads = $itemPriceUploads->where($field, $value);
                    break;
            }
        }

        if(empty($data['sort'])){
            $data['sort'] = (new ItemPriceUploads)->getTable().'.updated_at';
        }
        $sortField = $data['sort'];
        $sortDirection = !empty($data['direction'])?$data['direction']:'desc';
        $itemPriceUploads = $itemPriceUploads->orderBy($sortField, $sortDirection);

        if(empty($data['getCount'])){
            if($perPage==0){
                $itemPriceUploads = $itemPriceUploads->get();
            }
            else{
                $current_page = empty($data['current_page'])?1:$data['current_page'];
                $itemPriceUploads = $itemPriceUploads->paginate($perPage, ['*'], 'page', $current_page);
            }
        }
        else{
            $itemPriceUploads = $itemPriceUploads->count();
        }

        return response()->json([
            'data' => $itemPriceUploads,
            'requestData' => $data,
        ], 200);
    }

    public function downloadPrice(){
        // $itemLists = UserItemService::getItemLists();
        $itemPrices = ItemPrices::get();

        $PhpOfficeService = new PhpOfficeService();
        $data = [
            'A1' => 'item_id',
            'B1' => 'tag',
            'C1' => 'currency_item_id',
            'D1' => 'price',
        ];
        $row = 2;
        $cells = 0;
        foreach ($itemPrices as $key => $itemPrice) {
            $data[$PhpOfficeService->num2alpha($cells) . $row] = $itemPrice->item_id;
            $cells++;
            $data[$PhpOfficeService->num2alpha($cells) . $row] = $itemPrice->tag;
            $cells++;
            $data[$PhpOfficeService->num2alpha($cells) . $row] = $itemPrice->currency_item_id;
            $cells++;
            $data[$PhpOfficeService->num2alpha($cells) . $row] = $itemPrice->price;
            $cells++;

            $row++;
            $cells = 0;
        }
        $fileName = 'item_price.xlsx';
        $format = [];

        $download_url = $PhpOfficeService->export($fileName, $data, $format, 'temp');

        return response()->json([
            'data' => $download_url,
        ], 200);
    }

    public function new(Request $request)
    {
        $request_data = $request->input();

        if(!$request->hasFile('new_document')) return response()->json(['message'=>__('檔案錯誤'), 'field'=>'new_document'], 422);

        if($request->hasFile('new_document')){

            $result = FileService::upload_file($request->file('new_document'), 'itemp_price');

            $itemPriceUpload = new ItemPriceUploads;
            if($result){
                $itemPriceUpload->file_path = $result['file_path'];
                $itemPriceUpload->file_name = $result['file_name'];
                $itemPriceUpload->file_ext = $result['file_ext'];
                $itemPriceUpload->success = 0;
                $itemPriceUpload->fail = 0;
                $itemPriceUpload->updated_name = $request_data['updated_name'];
                $itemPriceUpload->save();

                $excel_data = (new PhpOfficeService)->import($request->file('new_document'), [], 0, 0);

                $updateOrder = [];
                $inserDetail = [];
                $inserItemPrice = [];
                $title = [];
                foreach ($excel_data as $index => $data) {
                    if($index==0){
                        foreach ($data as $key => $value) {
                            $title[$key] = $value;
                        }
                        continue;
                    }

                    $keyData = [];
                    foreach ($data as $key => $value) {
                        $keyData[$title[$key]] = $value;
                    }

                    $temp = [];
                    $temp['item_price_upload_id'] = $itemPriceUpload->id;
                    $temp['item_id'] = $keyData['item_id'];
                    $temp['tag'] = $keyData['tag'];
                    $temp['currency_item_id'] = $keyData['currency_item_id'];
                    $temp['price'] = $keyData['price'];
                    $temp['created_at'] = Carbon::now();
                    $temp['updated_at'] = Carbon::now();

                    $itemPriceUpload->success++;
                    $inserDetail[] = $temp;

                    $priceTemp = [];
                    $priceTemp['item_id'] = $keyData['item_id'];
                    $priceTemp['tag'] = $keyData['tag'];
                    $priceTemp['currency_item_id'] = $keyData['currency_item_id'];
                    $priceTemp['price'] = $keyData['price'];
                    $priceTemp['created_at'] = Carbon::now();
                    $priceTemp['updated_at'] = Carbon::now();

                    $inserItemPrice[] = $priceTemp;
                }

                \DB::beginTransaction();
                try {
                    $itemPriceUpload->save();

                    ItemPrices::query()->delete();
                    if($inserDetail) ItemPriceUploadDetails::insert($inserDetail);
                    if($inserItemPrice) ItemPrices::insert($inserItemPrice);

                    \DB::commit();
                    return response()->json(['message'=>'上傳成功:'.$itemPriceUpload->success.',上傳失敗:'.$itemPriceUpload->fail,], 200);

                } catch (Throwable $e) {
                    \DB::rollback();
                    \Log::error("[ItemPriceUpload]上傳失敗: {$e->getMessage()}");
                    return response()->json(['message'=>__('資料儲存失敗'),], 500);
                }
            }
            else{
                return response()->json(['message'=>__('檔案上傳失敗'), 'field'=>'new_document'], 422);
            }
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json([
            'data' => OrderImportNews::with('orderImportNewDetails')
                        ->where('id', $id)->first()?:new OrderImportNews,
        ], 200);
    }

}
