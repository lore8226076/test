<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RedeemCode;
use App\Models\UserInboxEntries;
use App\Models\UserRedeemCode;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RedeemController extends Controller
{
    public $key;
    public $iv;

    public function __construct(Request $request)
    {
        $this->key = env('REDEEM_KEY');
        $this->iv  = env('REDEEM_IV');

        $origin         = $request->header('Origin');
        $referer        = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['getList', 'redeem', 'create']]);
        }
    }

    // 取得兌換碼列表
    public function getList(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $redeemCodes = RedeemCode::query();

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
                        $redeemCodes = $redeemCodes->where($field, $value);
                    }
                    break;
                case 'status':
                    $redeemCodes = $redeemCodes->whereIn($field, explode('_', $value));
                    break;
                case 'pay_month':
                    if (! empty($value)) {
                        $pay_start     = $value . "-01";
                        $pay_end       = date('Y-m-t', strtotime($pay_start));
                        $redeemCodes = $redeemCodes->whereBetween('pay_date', [$pay_start, $pay_end]);
                    }
                    break;
                case 'name':
                    if (! empty($value)) {
                        $redeemCodes = $redeemCodes->where('name', "LIKE", '%' . $value . '%');
                    }
                    break;
                default:
                    $redeemCodes = $redeemCodes->where($field, $value);
                    break;
            }
        }

        // 預設排序為 id desc
        if (empty($data['sort'])) {
            $data['sort'] = (new RedeemCode)->getTable() . '.id';
            $data['direction'] = 'desc';
        }
        $sortField     = $data['sort'];
        $sortDirection = ! empty($data['direction']) ? $data['direction'] : 'desc';
        $redeemCodes = $redeemCodes->orderBy($sortField, $sortDirection);

        if (empty($data['getCount'])) {
            if ($perPage == 0) {
                $redeemCodes = $redeemCodes->get();
            } else {
                $current_page  = empty($data['current_page']) ? 1 : $data['current_page'];
                $redeemCodes = $redeemCodes->paginate($perPage, ['*'], 'page', $current_page);
            }
        } else {
            $redeemCodes = $redeemCodes->count();
        }
        return response()->json(['data' => $redeemCodes], 200);
    }

    // 新增兌換碼
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string',
            'code'       => 'required|string',
            'start_at'   => 'nullable|date',
            'end_at'     => 'nullable|date',
            'rewards'    => 'nullable|string',
            'memo'       => 'nullable|string',
        ]);

        $data['rewards'] = json_decode($data['rewards'] ?? '[]', true);
        $redeemCode = RedeemCode::create($data);
        return response()->json(['success' => true, 'data' => $redeemCode]);
    }

    // 刪除兌換碼
    public function delete($id)
    {
        $redeemCode = RedeemCode::findOrFail($id);
        $redeemCode->softDelete();
        return response()->json(['success' => true]);
    }

    // 兌換兌換碼
    public function redeem(Request $request)
    {
        $payload = $request->input('payload');
        if (empty($payload)) {
            return response()->json(['success' => false, 'message' => '沒有收到 payload 參數'], 400);
        }

        $json = $this->decrypt($payload);
        if (empty($json)) {
            return response()->json(['success' => false, 'message' => 'payload 解密失敗，請檢查加密格式與 key/iv'], 400);
        }

        $data = json_decode($json, true);
        if (empty($data) || ! is_array($data)) {
            return response()->json(['success' => false, 'message' => 'payload 內容格式錯誤'], 400);
        }

        $validator = Validator::make($data, [
            'uid'  => 'required|integer',
            'code' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $redeemCode = RedeemCode::where('code', strtoupper($data['code']))->first();
        if (! $redeemCode) {
            return response()->json(['success' => false, 'message' => '兌換碼不存在'], 404);
        }
        if ($redeemCode->isExpired()) {
            return response()->json(['success' => false, 'message' => '兌換碼已過期'], 400);
        }
        if (UserRedeemCode::where('uid', $data['uid'])->where('redeem_code_id', $redeemCode->id)->exists()) {
            return response()->json(['success' => false, 'message' => '您已兌換過此兌換碼'], 400);
        }

        // 檢查使用者
        $user = Users::where('uid', $data['uid'])->first();
        if (! $user) {
            return response()->json(['success' => false, 'message' => '使用者不存在'], 404);
        }

        $userRedeemCode = UserRedeemCode::create([
            'uid'             => $data['uid'],
            'redeem_code_id'  => $redeemCode->id,
            'redeemed_at'     => now(),
            'reward_snapshot' => $redeemCode->rewards,
        ]);

        $rewards = $this->convertRewards($redeemCode->rewards);

        if (empty($rewards)) {
            \Log::error('兌換失敗：獎勵格式錯誤', ['rewards' => $rewards]);
            return response()->json(['success' => false, 'message' => '兌換失敗：獎勵格式錯誤'], 400);
        }

        // 寫入user_inbox
        try {
            UserInboxEntries::create([
                'uid'                => $data['uid'],
                'inbox_messages_id'  => 8,
                'status'             => 'unread',
                'custom_attachments' => $rewards,
                'attachment_status'  => 'unclaimed',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '兌換失敗：' . $e->getMessage()], 500);
        }
        return response()->json(['success' => true, 'message' => '兌換成功']);
    }

    // 取得兌換碼使用紀錄
    public function getRedeemHistory($uid)
    {
        $history = UserRedeemCode::where('uid', $uid)->get();
        return response()->json($history);
    }

    // 加密變數
    public function encrypt($data)
    {
        $key       = $this->key;
        $iv        = $this->iv;
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return $encrypted;
    }

    // 解密變數
    public function decrypt($data)
    {
        $key       = $this->key;
        $iv        = $this->iv;
        $decrypted = openssl_decrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return $decrypted;
    }

    private function convertRewards($input)
    {
        $output = [];
        foreach ($input as $reward) {
            if (is_array($reward) && count($reward) === 1) {
                $itemId   = array_key_first($reward);
                $amount   = $reward[$itemId];
                $output[] = [
                    'item_id' => $itemId,
                    'amount'  => $amount,
                ];
            } elseif (is_array($reward) && isset($reward['item_id']) && isset($reward['amount'])) {
                $output[] = $reward;
            }
        }
        return $output;
    }

}
