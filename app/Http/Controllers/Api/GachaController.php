<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gachas;
use App\Service\ErrorService;
use App\Service\UserGachaOrderService;
use App\Service\UserItemService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GachaController extends Controller
{
    public function __construct(Request $request)
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['']]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 20);

        $gachas = Gachas::with([
            'gachaDetails' => function ($q) {
                $q->with('itemDetail');
            },
        ]);

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
                    if (! empty($value)) {
                        $gachas = $gachas->where($field, $value);
                    }
                    break;
                case 'status':
                    $gachas = $gachas->whereIn($field, explode('_', $value));
                    break;
                case 'pay_month':
                    if (! empty($value)) {
                        $pay_start = $value.'-01';
                        $pay_end = date('Y-m-t', strtotime($pay_start));
                        $gachas = $gachas->whereBetween('pay_date', [$pay_start, $pay_end]);
                    }
                    break;
                case 'name':
                    if (! empty($value)) {
                        $gachas = $gachas->where($field, 'LIKE', '%'.$value.'%');
                    }
                    break;
                default:
                    $gachas = $gachas->where($field, $value);
                    break;
            }
        }

        if (empty($data['sort'])) {
            $data['sort'] = (new Gachas)->getTable().'.updated_at';
        }
        $sortField = $data['sort'];
        $sortDirection = ! empty($data['direction']) ? $data['direction'] : 'desc';
        $gachas = $gachas->orderBy($sortField, $sortDirection);

        if (empty($data['getCount'])) {
            if ($perPage == 0) {
                $gachas = $gachas->get();
            } else {
                $current_page = empty($data['current_page']) ? 1 : $data['current_page'];
                $gachas = $gachas->paginate($perPage, ['*'], 'page', $current_page);
            }
        } else {
            $gachas = $gachas->count();
        }

        // 新增當前扭蛋機總機率
        foreach ($gachas as $gacha) {
            $gacha->total_percent = $gacha->gachaDetails->sum('percent');
        }

        return response()->json([
            'data' => $gachas,
            'requestData' => $data,
        ], 200);
    }

    public function liveList(Request $request)
    {
        $data = $request->input();

        $user = auth()->guard('api')->user();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        // 初始化貨幣
        UserItemService::initCurrency($user, 100);

        $gachas = Gachas::with('gachaDetails')
            ->where('is_active', 1)
            ->where(function ($query) {
                $query->whereNull('end_time')              // end_time 為 NULL 時不過濾
                    ->orWhere('end_time', '>', Carbon::now()); // end_time 大於現在時間
            })
            ->get();
        $gachas->each(function ($gacha) use ($user) {
            if ($gacha->max_times == -1) {
                $gacha->pity_info = -1;
            } else {
                $gacha->pity_info = UserGachaOrderService::getPityInfo($user, $gacha);
            }

            // 免費抽取資訊
            $gacha->user_free_draw_info = UserGachaOrderService::getFreeDrawInfoByGacha($user->uid, $gacha);

            // 沒有對應道具轉換成特定道具
            $subGachaOrderItem = UserGachaOrderService::getSubGachaOrderItem($user, $gacha);
            $gacha->currency_item_id = $subGachaOrderItem['item_id'] ?? $gacha->currency_item_id;
            if (isset($subGachaOrderItem['one_price'])) {
                $gacha->one_price = $subGachaOrderItem['one_price'];
            }
            if (isset($subGachaOrderItem['ten_price'])) {
                $gacha->ten_price = $subGachaOrderItem['ten_price'];
            }
        });

        return response()->json([
            'data' => $gachas,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 驗證請求參數
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'localization_name' => 'required|string|max:255',
            'type' => 'required|integer|in:1,2,3,4',
            'currency_item_id' => 'required|numeric|between:100,110',
            'one_price' => 'required|numeric|min:1',
            'ten_price' => 'required|numeric|min:1',
            'max_times' => 'required|integer|min:-1',
            'free_draw_times' => 'nullable|integer|min:0',
            'is_permanent' => 'required|boolean',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'is_active' => 'required|numeric',
        ]);

        if ($validatedData['is_permanent'] == 1) {
            $validatedData['start_time'] = null;
            $validatedData['end_time'] = null;
        }
        unset($validatedData['is_permanent']);

        $gacha = Gachas::create($validatedData);

        return response()->json([
            'message' => '扭蛋池已成功新增',
            'data' => $gacha,
        ], 201);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, $id)
    {
        // 要更新的資料
        $gacha = Gachas::find($id);
        if (! $gacha) {
            return response()->json(['message' => '找不到資料'], 404);
        }

        //  驗證規則
        $rules = [
            'name' => 'required|string|max:255',
            'localization_name' => 'nullable|string|max:255',
            'type' => 'nullable|integer|in:1,2,3,4',
            'is_permanent' => 'required|boolean',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'currency_item_id' => 'nullable|integer|min:100',
            'one_price' => 'nullable|numeric|min:1',
            'ten_price' => 'nullable|numeric|min:10',
            'max_times' => 'nullable|integer|min:-1',
            'free_draw_times' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ];

        // 檢查是否 **只更新 `is_active`**
        $requestKeys = array_keys($request->all());
        $isOnlyUpdatingStatus = count($requestKeys) === 1 && in_array('is_active', $requestKeys, true);

        try {
            $validated = $request->validate($isOnlyUpdatingStatus ? ['is_active' => 'required|boolean'] : $rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('驗證失敗', [
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ]);

            return response()->json(['message' => '驗證失敗', 'errors' => $e->errors()], 422);
        }

        $updateData = array_intersect_key($validated, $rules);

        if (! $isOnlyUpdatingStatus) {
            if ($updateData['is_permanent'] == 1) {
                $updateData['start_time'] = null;
                $updateData['end_time'] = null;
            }
            unset($updateData['is_permanent']);
        }

        if (empty($updateData)) {
            return response()->json(['message' => '沒有提供有效的更新資料'], 400);
        }

        //  更新資料
        try {
            $gacha->update($updateData);

            return response()->json([
                'message' => $isOnlyUpdatingStatus ? '狀態更新成功' : '資料更新成功',
                'gacha' => $gacha->refresh(), // 重新讀取最新資料
            ]);
        } catch (\Exception $e) {
            \Log::error('更新失敗', [
                'message' => $e->getMessage(),
                'data' => $updateData,
            ]);

            return response()->json(['message' => '更新失敗', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 取得單筆資料
     */
    public function show($id)
    {
        $gacha = Gachas::with('gachaDetails')->find($id);

        if (! $gacha) {
            return response()->json(['message' => '找不到資料！'], 404);
        }

        return response()->json(['data' => $gacha->toArray()], 200);
    }
}
