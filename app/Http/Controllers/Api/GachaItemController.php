<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GachaDetails as GachaItem;
use Illuminate\Http\Request;

class GachaItemController extends Controller
{
    public function __construct(Request $request)
    {
        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);

        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => []]);
        }
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $gachaItems = GachaItem::with('gacha');

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
                case 'item_id':
                    if (! empty($value)) {
                        $gachaItems = $gachaItems->whereRaw('LOWER(' . $field . ') like ?', ['%' . strtolower($value) . '%']);
                    }
                    break;
                case 'gacha_id':
                    if (! empty($value)) {
                        $gachaItems = $gachaItems->where('gacha_id', $value);
                    }
                    break;
                default:
                    if (! empty($value)) {
                        $gachaItems = $gachaItems->where($field, $value);
                    }
                    break;
            }
        }

        if (empty($data['sort'])) {
            $data['sort'] = (new GachaItem)->getTable() . '.id';
        }
        $sortField     = $data['sort'];
        $sortDirection = ! empty($data['direction']) ? $data['direction'] : 'asc';
        $gachaItems    = $gachaItems->orderBy($sortField, $sortDirection);

        if ($perPage == 0) {
            $gachaItems = $gachaItems->get();
        } else {
            $current_page = empty($data['current_page']) ? 1 : $data['current_page'];
            $gachaItems   = $gachaItems->paginate($perPage, ['*'], 'page', $current_page);
        }

        if (isset($data['to_key_value'])) {
            $gachaItems   = $gachaItems->get();
            $to_key_value = explode('-', $data['to_key_value']);
            $temp         = [];
            foreach ($gachaItems as $user) {
                $temp[$user->{$to_key_value[0]}] = $user->{$to_key_value[1]};
            }
            $gachaItems = $temp;
        }

        return response()->json([
            'data'        => $gachaItems,
            'requestData' => $data,

        ], 200);
    }

    // 取得單筆資料
    public function show($item_id)
    {
        $gachaItem = GachaItem::with('gacha')->where('item_id', $item_id)->firstOrFail();
        return response()->json([
            'data' => $gachaItem,
        ], 200);
    }

    public function store(Request $request)
    {
        // 驗證請求參數
        $validatedData = $request->validate($this->rules());

        // 計算扭蛋池總機率
        $totalPercent = $this->calculateTotalPercent($validatedData['gacha_id']);
        if ($totalPercent + $validatedData['percent'] > 100) {
            return response()->json(['message' => '當前總機率為' . $totalPercent . '%，扭蛋池總機率不能超過100%'], 422);
        }

        $gachaItem = GachaItem::create($validatedData);
        $gachaItem = GachaItem::with('itemDetail')->find($gachaItem->id);

        return response()->json([
            'message' => '扭蛋已成功新增',
            'data'    => $gachaItem,
        ], 201);
    }

    // 更新資料
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate($this->rules());

        $gachaItem = GachaItem::with('itemDetail')->find($id);

        // 計算扭蛋池總機率(排除當前項目)
        $totalPercent = $this->calculateTotalPercent($validatedData['gacha_id'], $id);
        if ($totalPercent + $validatedData['percent'] > 100) {
            return response()->json(['message' => '其他扭蛋總機率為' . $totalPercent . '%，扭蛋池總機率不能超過100%'], 422);
        }

        $gachaItem->update($validatedData);

        return response()->json([
            'message' => '扭蛋已成功更新',
            'data'    => $gachaItem,
        ], 200);
    }

    public function destroy($id)
    {
        $gachaItem = GachaItem::find($id);
        $gachaItem->delete();
        return response()->json([
            'message' => '扭蛋已成功刪除',
        ], 200);
    }

    // 計算扭蛋池總機率
    public function calculateTotalPercent($gacha_id, $exclude_id = null)
    {
        $query = GachaItem::where('gacha_id', $gacha_id);
        if ($exclude_id) {
            $query = $query->where('id', '!=', $exclude_id);
        }
        $totalPercent = $query->sum('percent');
        return $totalPercent;
    }

    // 驗證規則
    public function rules()
    {
        return [
            'gacha_id'   => 'required|numeric|exists:gachas,id',
            'item_id'    => 'required|numeric|min:1|max:9999999',
            'percent'    => 'required|numeric|min:0.01',
            'qty'        => 'required|numeric|min:1',
            'guaranteed' => 'required|boolean',
        ];
    }

}
