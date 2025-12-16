<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderApplyUpdates;
use App\Models\OrderCheckins;
use App\Models\OrderFinishFiles;
use App\Models\Orders;
use App\Models\OrderSignReceiptFiles;
use App\Models\OrderSvFiles;
use App\Models\StoreCustomers;
use App\Service\FileService;
use App\Service\StoreCustomerNotifyService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class OrderController extends Controller
{
    public function __construct(Request $request)
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['store']]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 20);

        $orders = Orders::with('cityAreaList')
            ->with('orderApplyUpdate')
            ->with('storeCustomer.bankList');

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
                case 'store_customers.name':
                case 'name':
                case 'store_customers.account':
                case 'account':
                case 'no':
                    if (! empty($value)) {
                        $orders = $orders->where(DB::raw('UPPER('.$field.')'), 'LIKE', '%'.strtoupper($value).'%');
                    }
                    break;
                case 'occupation_id':
                case 'repair_type':
                case 'place_type':
                case 'city_area_list_id':
                    if (! empty($value)) {
                        $orders = $orders->where($field, $value);
                    }
                    break;
                case 'status':
                    $status = explode('_', $value);
                    $orders = $orders->whereIn($field, $status);
                    break;
                default:
                    $orders = $orders->where($field, $value);
                    break;
            }
        }

        if (empty($data['sort'])) {
            $data['sort'] = (new Orders)->getTable().'.updated_at';
        }
        $sortField = $data['sort'];
        $sortDirection = ! empty($data['direction']) ? $data['direction'] : 'desc';
        $orders = $orders->orderBy($sortField, $sortDirection);

        if (empty($data['getCount'])) {
            if ($perPage == 0) {
                $orders = $orders->get();
            } else {
                $current_page = empty($data['current_page']) ? 1 : $data['current_page'];
                $orders = $orders->paginate($perPage, ['*'], 'page', $current_page);
            }
        } else {
            $orders = $orders->count();
        }

        return response()->json([
            'data' => $orders,
            'requestData' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'no' => 'required',
            'repair_type' => 'required',
            'place_type' => 'required',
            'need_man' => 'required',
            'city_area_list_id' => 'required',
            'address' => 'required',
            'contact_last_name' => 'required',
            'contact_name' => 'required',
            'contact_gender' => 'required',
            'contact_phone' => 'required',
            'need_sv' => 'required',
        ]);

        $data = $request->input();
        foreach ($data as $key => $value) {
            if ($value === 'null') {
                $data[$key] = null;
            }

            if (in_array($key, ['fees', 'sv_items'])) {
                if (! empty($value) && is_string($value)) {
                    $data[$key] = json_decode($value, true);
                }
            }
        }

        $message = '';
        if (empty($data['no'])) {
            return response()->json(['message' => __('工單編號錯誤'), 'field' => 'no'], 422);
        }
        if (Orders::where('no', $data['no'])->count()) {
            return response()->json(['message' => __('工單編號已存在'), 'field' => 'no'], 422);
        }

        if (empty($data['repair_type'])) {
            return response()->json(['message' => __('安裝服務類型錯誤'), 'field' => 'repair_type'], 422);
        }
        if (empty($data['place_type'])) {
            return response()->json(['message' => __('地點類型錯誤'), 'field' => 'place_type'], 422);
        }
        if (empty($data['need_man'])) {
            return response()->json(['message' => __('需求師傅人數錯誤'), 'field' => 'need_man'], 422);
        }
        if (empty($data['city_area_list_id'])) {
            return response()->json(['message' => __('工程地區錯誤'), 'field' => 'city_area_list_id'], 422);
        }
        if (empty($data['address'])) {
            return response()->json(['message' => __('詳細地址錯誤'), 'field' => 'address'], 422);
        }
        if (empty($data['contact_last_name'])) {
            return response()->json(['message' => __('聯絡人姓錯誤'), 'field' => 'contact_last_name'], 422);
        }
        if (empty($data['contact_name'])) {
            return response()->json(['message' => __('聯絡人名錯誤'), 'field' => 'contact_name'], 422);
        }
        if (! isset($data['contact_gender'])) {
            return response()->json(['message' => __('聯絡人稱謂錯誤'), 'field' => 'contact_gender'], 422);
        }
        if (empty($data['contact_phone'])) {
            return response()->json(['message' => __('聯絡人電話錯誤'), 'field' => 'contact_phone'], 422);
        }

        if (! isset($data['need_sv'])) {
            return response()->json(['message' => __('SV需求錯誤'), 'field' => 'need_sv'], 422);
        }
        if (! $data['need_sv']) {
            // 不需SV 需提供詳細施工以及報價資訊
            if (empty($data['pre_work_start_date'])) {
                return response()->json(['message' => __('預計開工日錯誤'), 'field' => 'pre_work_start_date'], 422);
            }
            if (empty($data['pre_work_end_date'])) {
                return response()->json(['message' => __('預計開工日錯誤'), 'field' => 'pre_work_end_date'], 422);
            }
            if (! isset($data['pre_work_days'])) {
                return response()->json(['message' => __('預計工日'), 'field' => 'pre_work_days'], 422);
            }
            if (! isset($data['pre_work_hours'])) {
                return response()->json(['message' => __('預計工時'), 'field' => 'pre_work_hours'], 422);
            }
            // if(empty($data['pre_times'])) return response()->json(['message'=>__('預計完工日錯誤'), 'field'=>'pre_times'], 422);

            if (empty($data['fees'])) {
                return response()->json(['message' => __('報價錯誤'), 'field' => 'fees'], 422);
            }
            foreach ($data['fees'] as $feeData) {
                if (empty($feeData['fee'])) {
                    return response()->json(['message' => __('報價錯誤，T+n都必須填寫金額'), 'field' => 'fees'], 422);
                }
            }
            // if(!$request->hasFile('new_fee_report')) return response()->json(['message'=>__('報價單錯誤'), 'field'=>'new_fee_report'], 422);
            // if(empty($data['sv_line'])) return response()->json(['message'=>__('放線路線錯誤'), 'field'=>'sv_line'], 422);
            // if(empty($data['sv_items'])) return response()->json(['message'=>__('SV Items錯誤'), 'field'=>'sv_items'], 422);
            if (! $request->hasFile('new_sv_path')) {
                return response()->json(['message' => __('相關檔案錯誤'), 'field' => 'new_sv_path'], 422);
            }
        }

        $order = new Orders;
        $order->fill($data);

        $order->occupation_id = 1; // 預設工種1

        if (! empty($order->fees) && is_array($order->fees)) {
            $order->fees = json_encode($order->fees);
        }
        if (! empty($order->sv_items) && is_array($order->sv_items)) {
            $order->sv_items = json_encode($order->sv_items);
        }
        \Log::info(json_encode($order));
        if ($order->save()) {
            $need_save = false;
            if ($request->hasFile('new_document')) {
                $result = FileService::upload_file($request->file('new_document'), 'order_document', $order->id);
                if ($result) {
                    $order->document_path = $result['file_path'].$result['file_name'];
                    $need_save = true;
                }
            }
            if ($request->hasFile('new_fee_report')) {
                $result = FileService::upload_file($request->file('new_fee_report'), 'order_fee_report', $order->id);
                if ($result) {
                    $order->fee_report = $result['file_path'].$result['file_name'];
                    $need_save = true;
                }
            }
            if ($request->hasFile('new_sv_path')) {
                $result = FileService::upload_file($request->file('new_sv_path'), 'order_sv_path', $order->id);
                if ($result) {
                    $order->sv_path = $result['file_path'].$result['file_name'];
                    $need_save = true;
                }
            }
            if ($need_save) {
                $order->save();
            }

            return response()->json(['message' => __('儲存成功'), 'data' => ['order' => $order]], 200);
        } else {
            return response()->json(['message' => __('儲存失敗'), 'data' => $data], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json([
            'data' => Orders::with('cityAreaList')->with('storeCustomer')
                ->with('orderCheckins')
                ->with('orderSignReceiptFiles')
                ->with('orderFinishFiles')
                ->with('orderSvFiles')->where('id', $id)->first() ?: new Orders,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            if ($value === 'null') {
                $data[$key] = null;
            }

            if (in_array($key, ['fees', 'sv_items'])) {
                if (! empty($value) && is_string($value)) {
                    $data[$key] = json_decode($value, true);
                }
            }
        }
        \Log::info(json_encode($data));
        if (isset($data['no'])) {
            if (empty($data['no'])) {
                return response()->json(['message' => __('工單編號錯誤'), 'field' => 'no'], 422);
            }
            if (Orders::where('no', $data['no'])->where('id', '!=', $id)->count()) {
                return response()->json(['message' => __('工單編號已存在'), 'field' => 'no'], 422);
            }
        }

        if (isset($data['repair_type']) && empty($data['repair_type'])) {
            return response()->json(['message' => __('安裝服務類型錯誤'), 'field' => 'repair_type'], 422);
        }
        if (isset($data['place_type']) && empty($data['place_type'])) {
            return response()->json(['message' => __('地點類型錯誤'), 'field' => 'place_type'], 422);
        }
        if (isset($data['need_man']) && empty($data['need_man'])) {
            return response()->json(['message' => __('需求師傅人數錯誤'), 'field' => 'need_man'], 422);
        }
        if (isset($data['city_area_list_id']) && empty($data['city_area_list_id'])) {
            return response()->json(['message' => __('工程地區錯誤'), 'field' => 'city_area_list_id'], 422);
        }
        if (isset($data['address']) && empty($data['address'])) {
            return response()->json(['message' => __('詳細地址錯誤'), 'field' => 'address'], 422);
        }
        if (isset($data['contact_last_name']) && empty($data['contact_last_name'])) {
            return response()->json(['message' => __('聯絡人姓錯誤'), 'field' => 'contact_last_name'], 422);
        }
        if (isset($data['contact_name']) && empty($data['contact_name'])) {
            return response()->json(['message' => __('聯絡人名錯誤'), 'field' => 'contact_name'], 422);
        }
        if (isset($data['contact_phone']) && empty($data['contact_phone'])) {
            return response()->json(['message' => __('聯絡人電話錯誤'), 'field' => 'contact_phone'], 422);
        }

        $order = Orders::find($id);

        if (isset($data['status'])) {
            if ($order->status != $data['status'] && $data['status'] == 4) {
                if (empty($data['fees'])) {
                    return response()->json(['message' => __('報價錯誤'), 'field' => 'fees'], 422);
                }
                foreach ($data['fees'] as $feeData) {
                    if (empty($feeData['fee'])) {
                        return response()->json(['message' => __('報價錯誤，T+n都必須填寫金額'), 'field' => 'fees'], 422);
                    }
                }
                // if(empty($order->fee_report) && !$request->hasFile('new_fee_report')) return response()->json(['message'=>__('報價單錯誤'), 'field'=>'new_fee_report'], 422);
            }
            if ($order->status == 6 && $order->status != $data['status']) {
                if (empty($data['redate_status'])) {
                    return response()->json(['message' => __('申請改期狀態錯誤'), 'field' => 'redate_status'], 422);
                }

                $orderApplyUpdate = OrderApplyUpdates::where('order_id', $order->id)->where('status', 0)->first();
                if (empty($orderApplyUpdate)) {
                    return response()->json(['message' => __('申請改期資料錯誤'), 'field' => 'orderApplyUpdate'], 422);
                }

                $orderApplyUpdate->status = $data['redate_status'];
                if (isset($data['updated_name'])) {
                    $orderApplyUpdate->updated_name = $data['updated_name'];
                }
            }

        }

        if (! $order) {
            return response()->json(['message' => __('資料錯誤')], 404);
        }
        $order->fill($data);

        if ($request->hasFile('new_document')) {
            $result = FileService::upload_file($request->file('new_document'), 'order_document', $order->id);
            if ($result) {
                $order->document_path = $result['file_path'].$result['file_name'];
            }
        }
        if ($request->hasFile('new_fee_report')) {
            $result = FileService::upload_file($request->file('new_fee_report'), 'order_fee_report', $order->id);
            if ($result) {
                $order->fee_report = $result['file_path'].$result['file_name'];
            }
        }
        if ($request->hasFile('new_sv_path')) {
            $result = FileService::upload_file($request->file('new_sv_path'), 'order_sv_path', $order->id);
            if ($result) {
                $order->sv_path = $result['file_path'].$result['file_name'];
            }
        }
        if (isset($data['updated_name'])) {
            $order->updated_name = $data['updated_name'];
        }

        if (isset($orderApplyUpdate)) {
            if ($orderApplyUpdate->save()) {
                if ($orderApplyUpdate->status == 1) {
                    StoreCustomerNotifyService::agreeRedate($order);
                } elseif ($orderApplyUpdate->status == 2) {
                    StoreCustomerNotifyService::rejectRedate($order);
                }
            }
        }

        if ($order->save()) {
            // \Log::info(json_encode($order));
            return response()->json(['message' => __('儲存成功'), 'data' => ['order' => $order]], 200);
        } else {
            return response()->json(['message' => __('儲存失敗')], 500);
        }
    }

    public function user_get(Request $request, $id)
    {
        $data = $request->input();

        $order = Orders::find($id);

        if ($order->status != 1) {
            return response()->json(['message' => __('此工單目前不可認領'), 'field' => 'status'], 422);
        }

        $storeCustomer = StoreCustomers::where('id', auth()->guard('api')->user()->id)->where('is_active', 1)->first();
        if (empty($storeCustomer)) {
            return response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }

        $order->store_customer_id = $storeCustomer->id;
        if ($order->need_sv) {
            $order->status = 2; // 待SV
        } else {
            $order->status = 7; // 待施工
            if (empty($data['confirm_t'])) {
                return response()->json(['message' => __('報價選擇T天數錯誤'), 'field' => 'confirm_t'], 422);
            }
            if (empty($data['confirm_fee'])) {
                return response()->json(['message' => __('報價選擇金額錯誤'), 'field' => 'confirm_fee'], 422);
            }

            $order->confirm_t = $data['confirm_t'];
            $order->confirm_fee = $data['confirm_fee'];
        }

        if ($order->save()) {
            return response()->json(['message' => __('接取成功'), 'data' => ['order' => $order]], 200);
        } else {
            return response()->json(['message' => __('接取失敗')], 500);
        }
    }

    public function sv(Request $request, $id)
    {
        $data = $request->input();

        $validator = Validator::make($data, [
            'new_sv_images' => 'required',
            'sv_line' => 'required',
            'sv_items' => 'required',
            'pre_work_start_date' => 'required',
            'pre_work_end_date' => 'required',
            'pre_work_days' => 'required',
            'pre_work_hours' => 'required',
        ]);

        $storeCustomer = StoreCustomers::where('id', auth()->guard('api')->user()->id)->where('is_active', 1)->first();
        if (empty($storeCustomer)) {
            return response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }

        if (empty($data['sv_items'])) {
            return response()->json(['message' => __('SV Items錯誤'), 'field' => 'sv_items'], 422);
        }
        // 不需SV 需提供詳細施工以及報價資訊
        if (empty($data['pre_work_start_date'])) {
            return response()->json(['message' => __('預計開工日錯誤'), 'field' => 'pre_work_start_date'], 422);
        }
        if (empty($data['pre_work_end_date'])) {
            return response()->json(['message' => __('預計開工日錯誤'), 'field' => 'pre_work_end_date'], 422);
        }
        if (! isset($data['pre_work_days'])) {
            return response()->json(['message' => __('預計工日'), 'field' => 'pre_work_days'], 422);
        }
        if (! isset($data['pre_work_hours'])) {
            return response()->json(['message' => __('預計工時'), 'field' => 'pre_work_hours'], 422);
        }

        if (! $request->hasFile('new_sv_images')) {
            return response()->json(['message' => __('請上傳SV相片'), 'field' => 'new_sv_images'], 422);
        }

        $order = Orders::where('id', $id)->where('store_customer_id', $storeCustomer->id)->first();
        if (empty($order)) {
            return response()->json(['message' => __('工單錯誤'), 'field' => 'auth_store_customer'], 422);
        }
        if (! in_array($order->status, [2, 3])) {
            return response()->json(['message' => __('狀態錯誤，不可提交SV'), 'field' => 'status'], 422);
        }

        $order->fill($data);
        $order->need_sv = 0; // 不須SV
        $order->status = 3; // 待報價
        $order->updated_name = $storeCustomer->name;
        if ($order->save()) {
            if ($request->hasFile('new_sv_images')) {
                $insert_svs = [];
                foreach ($request->file('new_sv_images') as $new_sv_image) {
                    $result = FileService::upload_file($new_sv_image, 'order_sv_images', $order->id);
                    if ($result) {
                        $insert_svs[] = [
                            'order_id' => $order->id,
                            'type' => 'image',
                            'file_name' => $result['file_name'],
                            'file_path' => $result['file_path'],
                            'file_ext' => $result['file_ext'],
                            'updated_name' => $order->updated_name,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                }

                if ($insert_svs) {
                    OrderSvFiles::insert($insert_svs);
                }
            }

            return response()->json(['message' => __('提交成功'), 'data' => []], 200);
        }

        return response()->json(['message' => __('提交失敗')], 500);
    }

    public function confirm_price(Request $request, $id)
    {
        $data = $request->input();

        $storeCustomer = StoreCustomers::where('id', auth()->guard('api')->user()->id)->where('is_active', 1)->first();
        if (empty($storeCustomer)) {
            return response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }

        $order = Orders::where('id', $id)->where('store_customer_id', $storeCustomer->id)->first();
        if (empty($order)) {
            return response()->json(['message' => __('工單錯誤'), 'field' => 'auth_store_customer'], 422);
        }
        if (! in_array($order->status, [4])) {
            return response()->json(['message' => __('狀態錯誤，不可確認報價'), 'field' => 'status'], 422);
        }

        if (empty($data['confirm_t'])) {
            return response()->json(['message' => __('報價選擇T天數錯誤'), 'field' => 'confirm_t'], 422);
        }
        if (empty($data['confirm_fee'])) {
            return response()->json(['message' => __('報價選擇金額錯誤'), 'field' => 'confirm_fee'], 422);
        }

        $order->status = 7; // 待施工
        $order->confirm_t = $data['confirm_t'];
        $order->confirm_fee = $data['confirm_fee'];

        if ($order->save()) {
            return response()->json(['message' => __('接取成功'), 'data' => ['order' => $order]], 200);
        } else {
            return response()->json(['message' => __('接取失敗')], 500);
        }
    }
    // 0 => __('草稿'),
    // 1 => __('發佈中'),
    // 2 => __('待SV'),
    // 3 => __('待報價'),
    // 4 => __('待師傅確認報價'),
    // 6 => __('申請改期'),
    // 7 => __('待施工'),
    // 8 => __('施工中'),
    // 9 => __('施工完成'),
    // 10 => __('待師傅簽署'),
    // 11 => __('待撥款'),
    // 12 => __('結案'),

    public function checkin(Request $request, $id)
    {
        $data = $request->input();

        $storeCustomer = StoreCustomers::where('id', auth()->guard('api')->user()->id)->where('is_active', 1)->first();
        if (empty($storeCustomer)) {
            return response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }

        $order = Orders::where('id', $id)->where('store_customer_id', $storeCustomer->id)->first();
        if (empty($order)) {
            return response()->json(['message' => __('工單錯誤'), 'field' => 'auth_store_customer'], 422);
        }
        if (! in_array($order->status, [7, 8])) {
            return response()->json(['message' => __('狀態錯誤，不可打卡'), 'field' => 'status'], 422);
        }

        if (! $request->hasFile('new_checkin')) {
            return response()->json(['message' => __('請上傳打卡相片'), 'field' => 'new_checkin'], 422);
        }

        $order->fill($data);
        $order->status = 8;
        $order->updated_name = $storeCustomer->name;
        if ($order->save()) {
            $result = FileService::upload_file($request->file('new_checkin'), 'order_checkin', $order->id);

            $insert_datas = [];
            $insert_datas[] = [
                'order_id' => $order->id,
                'type' => 'image',
                'file_name' => $result['file_name'],
                'file_path' => $result['file_path'],
                'file_ext' => $result['file_ext'],
                'updated_name' => $order->updated_name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            if ($insert_datas) {
                OrderCheckins::insert($insert_datas);
            }

            return response()->json(['message' => __('打卡成功'), 'data' => []], 200);
        }

        return response()->json(['message' => __('打卡失敗')], 500);
    }

    public function add_sign_receipt_file(Request $request, $id)
    {
        $data = $request->input();

        $storeCustomer = StoreCustomers::where('id', auth()->guard('api')->user()->id)->where('is_active', 1)->first();
        if (empty($storeCustomer)) {
            return response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }

        $order = Orders::where('id', $id)->where('store_customer_id', $storeCustomer->id)->first();
        if (empty($order)) {
            return response()->json(['message' => __('工單錯誤'), 'field' => 'auth_store_customer'], 422);
        }
        if (! in_array($order->status, [7, 8, 9])) {
            return response()->json(['message' => __('狀態錯誤，不可確認報價'), 'field' => 'status'], 422);
        }

        if (! $request->hasFile('new_sign_receipt_file')) {
            return response()->json(['message' => __('請上傳客戶簽收相片'), 'field' => 'new_sign_receipt_file'], 422);
        }

        $insert_datas = [];
        foreach ($request->file('new_sign_receipt_file') as $file) {
            $result = FileService::upload_file($file, 'order_sign_receipt_file', $order->id);
            if ($result) {
                $insert_datas[] = [
                    'order_id' => $order->id,
                    'type' => 'image',
                    'file_name' => $result['file_name'],
                    'file_path' => $result['file_path'],
                    'file_ext' => $result['file_ext'],
                    'updated_name' => $order->updated_name,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        if ($insert_datas) {
            OrderSignReceiptFiles::insert($insert_datas);
        }

        return response()->json(['message' => __('儲存成功'), 'data' => []], 200);
        // return response()->json(['message' => __('提交失敗')], 500);

    }

    public function add_finish_file(Request $request, $id)
    {
        $data = $request->input();

        $storeCustomer = StoreCustomers::where('id', auth()->guard('api')->user()->id)->where('is_active', 1)->first();
        if (empty($storeCustomer)) {
            return response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }

        $order = Orders::where('id', $id)->where('store_customer_id', $storeCustomer->id)->first();
        if (empty($order)) {
            return response()->json(['message' => __('工單錯誤'), 'field' => 'auth_store_customer'], 422);
        }
        if (! in_array($order->status, [7, 8, 9])) {
            return response()->json(['message' => __('狀態錯誤，不可新增完工相片'), 'field' => 'status'], 422);
        }

        if (! $request->hasFile('new_add_finish_file')) {
            return response()->json(['message' => __('請上傳完工相片'), 'field' => 'new_add_finish_file'], 422);
        }

        $insert_datas = [];
        foreach ($request->file('new_add_finish_file') as $file) {
            $result = FileService::upload_file($file, 'order_add_finish_file', $order->id);
            if ($result) {
                $insert_datas[] = [
                    'order_id' => $order->id,
                    'type' => 'image',
                    'file_name' => $result['file_name'],
                    'file_path' => $result['file_path'],
                    'file_ext' => $result['file_ext'],
                    'updated_name' => $order->updated_name,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        if ($insert_datas) {
            OrderFinishFiles::insert($insert_datas);
        }

        return response()->json(['message' => __('儲存成功'), 'data' => []], 200);

    }

    public function confirm_finish(Request $request, $id)
    {
        $data = $request->input();

        $storeCustomer = StoreCustomers::where('id', auth()->guard('api')->user()->id)->where('is_active', 1)->first();
        if (empty($storeCustomer)) {
            return response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }

        $order = Orders::where('id', $id)->where('store_customer_id', $storeCustomer->id)->first();
        if (empty($order)) {
            return response()->json(['message' => __('工單錯誤'), 'field' => 'auth_store_customer'], 422);
        }
        if (! in_array($order->status, [7, 8, 9])) {
            return response()->json(['message' => __('狀態錯誤，不可提報施工完成'), 'field' => 'status'], 422);
        }

        $order->fill($data);
        $order->status = 9;
        $order->finish_at = Carbon::now();
        $order->updated_name = $storeCustomer->name;
        if ($order->save()) {
            return response()->json(['message' => __('提報施工完成成功'), 'data' => []], 200);
        }

        return response()->json(['message' => __('提報施工完成失敗')], 500);
    }

    public function finish_sign(Request $request)
    {
        $data = $request->input();

        $storeCustomer = StoreCustomers::where('id', auth()->guard('api')->user()->id)->where('is_active', 1)->first();
        if (empty($storeCustomer)) {
            return response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }

        if (empty($data['second_password']) || $storeCustomer->second_password != $data['second_password']) {
            return response()->json(['message' => __('簽署失敗，認證密碼錯誤'), 'field' => 'second_password'], 422);
        }

        if (empty($data['order_ids']) || ! is_array($data['order_ids'])) {
            return response()->json(['message' => __('簽署失敗，工單ID錯誤'), 'field' => 'order_ids'], 422);
        }

        $orders = Orders::whereIn('id', $data['order_ids'])->get();
        if (empty($orders) || count($orders) != count($data['order_ids'])) {
            return response()->json(['message' => __('簽署失敗，工單數量比對錯誤'), 'field' => 'orders'], 422);
        }

        foreach ($orders as $order) {
            if ($order->status != 10) {
                return response()->json(['message' => __('簽署失敗，工單('.$order->no.')狀態錯誤'), 'field' => 'status'], 422);
            }
            if ($order->store_customer_id != $storeCustomer->id) {
                return response()->json(['message' => __('簽署失敗，工單('.$order->no.')錯誤'), 'field' => 'auth_store_customer'], 422);
            }
        }

        foreach ($orders as $order) {
            $order->status = 11; // 待撥款
            $order->finish_sign_at = Carbon::now();

            $finishSignAt = Carbon::parse($order->finish_sign_at);
            $order->pay_date = $finishSignAt->addDays($order->confirm_t)->toDateString();
            $order->updated_name = $storeCustomer->name;
            $order->save();
        }

        return response()->json(['message' => __('簽署成功'), 'data' => []], 200);
    }

    public function apply_redate(Request $request, $id)
    {
        $data = $request->input();

        $storeCustomer = StoreCustomers::where('id', auth()->guard('api')->user()->id)->where('is_active', 1)->first();
        if (empty($storeCustomer)) {
            return response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }

        $order = Orders::where('id', $id)->where('store_customer_id', $storeCustomer->id)->first();
        if (empty($order)) {
            return response()->json(['message' => __('工單錯誤'), 'field' => 'auth_store_customer'], 422);
        }
        if (! in_array($order->status, [7, 8])) {
            return response()->json(['message' => __('狀態錯誤，不可(重複)申請改期'), 'field' => 'status'], 422);
        }

        // if(OrderApplyUpdates::where('order_id', $order_id->id)->where('status', 0)->count()) return response()->json(['message'=>__('已申請'), 'field'=>'status'], 422);

        if (empty($data['redate_start'])) {
            return response()->json(['message' => __('預計開工日錯誤'), 'field' => 'redate_start'], 422);
        }
        if (empty($data['redate_end'])) {
            return response()->json(['message' => __('預計完工日錯誤'), 'field' => 'redate_end'], 422);
        }
        if (! isset($data['redate_days']) || ! is_numeric($data['redate_days'])) {
            return response()->json(['message' => __('預計需日錯誤'), 'field' => 'redate_days'], 422);
        }
        if (! isset($data['redate_hours']) || ! is_numeric($data['redate_hours'])) {
            return response()->json(['message' => __('預計需時錯誤'), 'field' => 'redate_hours'], 422);
        }
        if (empty($data['reason'])) {
            return response()->json(['message' => __('原因錯誤'), 'field' => 'reason'], 422);
        }

        $orderApplyUpdate = new OrderApplyUpdates;
        $orderApplyUpdate->fill($data);
        $orderApplyUpdate->order_id = $order->id;
        $orderApplyUpdate->updated_name = $storeCustomer->name;
        if ($orderApplyUpdate->save()) {
            $order->status = 6; // 申請改期
            $order->updated_name = $storeCustomer->name;
            if ($order->save()) {
                return response()->json(['message' => __('已申請改期，待審核'), 'data' => []], 200);
            }
        }

        return response()->json(['message' => __('申請改期失敗')], 500);
    }

    public function total_confirm_fee(Request $request)
    {
        $data = $request->input();

        if (empty($data['store_customer_id'])) {
            response()->json(['message' => __('加盟資料錯誤'), 'field' => 'store_customer'], 422);
        }
        if (empty($data['start_date'])) {
            response()->json(['message' => __('起始日錯誤'), 'field' => 'start_date'], 422);
        }
        if (empty($data['end_date'])) {
            response()->json(['message' => __('結束日錯誤'), 'field' => 'end_date'], 422);
        }

        $orders = Orders::where('store_customer_id', $data['store_customer_id'])
            ->whereIn('status', [11, 12])
            ->where('pay_date', '>=', $data['start_date'])
            ->where('pay_date', '<=', $data['end_date']);

        $result['total_fee'] = $orders->sum('confirm_fee');
        $result['total_count'] = $orders->count();

        return response()->json(['message' => '', 'data' => $result], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
