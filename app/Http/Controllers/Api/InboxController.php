<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InboxAttachments;
use App\Models\InboxMessages;
use App\Models\InboxTargets;
use App\Models\UserInboxEntries;
use App\Models\UserItemLogs;
use App\Models\Users;
use App\Service\ErrorService;
use App\Service\InboxService;
use App\Service\UserItemService;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    protected $inboxService;

    public function __construct(Request $request, InboxService $inboxService)
    {
        $this->inboxService = $inboxService;

        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $referrerDomain = parse_url($origin, PHP_URL_HOST) ?? parse_url($referer, PHP_URL_HOST);
        if ($referrerDomain != config('services.API_PASS_DOMAIN')) {
            $this->middleware('auth:api', ['except' => ['index']]);
        }
    }

    // 取得信件列表
    public function inboxList()
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $inboxMessages = $this->inboxService->getAvailableInboxMessages($uid);
        $this->inboxService->insertAllForUser($uid, $inboxMessages);

        // 取得優先顯示的信件ID
        $priorityIds = $this->inboxService->priorityIds();

        // 取得信件列表，套用優先排序
        $entries = UserInboxEntries::where('uid', $uid)
            ->with(['inbox' => function ($query) {
                $query->with('attachments');
            }])
            ->get();

        // 格式化並排序
        $inboxList = $entries->map(function ($entry) {
            return $this->formatInbox($entry);
        });

        // 使用 Collection 排序：優先 ID 在前（由小到大），其餘按建立時間由新到舊
        $inboxList = $inboxList->sort(function ($a, $b) use ($priorityIds) {
            $aId = $a['inbox_messages_id'] ?? 0;
            $bId = $b['inbox_messages_id'] ?? 0;
            $aIsPriority = in_array($aId, $priorityIds);
            $bIsPriority = in_array($bId, $priorityIds);

            // 優先信件排在前面
            if ($aIsPriority && ! $bIsPriority) {
                return -1;
            }
            if (! $aIsPriority && $bIsPriority) {
                return 1;
            }

            // 都是優先信件，按 priorityIds 陣列順序排序
            if ($aIsPriority && $bIsPriority) {
                $aIndex = array_search($aId, $priorityIds);
                $bIndex = array_search($bId, $priorityIds);

                return $aIndex <=> $bIndex;
            }

            // 都不是優先信件，按建立時間由新到舊
            return strtotime($b['created_at'] ?? 0) <=> strtotime($a['created_at'] ?? 0);
        })->values();

        return response()->json(['data' => $inboxList]);
    }

    /** 讀取信件 */
    public function inboxRead(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $userInboxId = $request->input('user_inbox_id');
        if (empty($userInboxId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0001'), 422);
        }

        // 更新狀態
        $entry = $this->inboxService->markAsRead($uid, $userInboxId);
        if (empty($entry)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0001'), 422);
        }
        // 重新排版信件資訊
        $inbox = $this->formatInbox($entry);

        return response()->json(['data' => $inbox]);
    }

    // 領取信件附件
    public function inboxClaimAttachment(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }
        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $userInboxId = $request->input('user_inbox_id');
        if (empty($userInboxId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0001'), 422);
        }
        // 檢查有信&已領取
        $userInboxEntries = $this->inboxService->getUserInboxEntry($uid, $userInboxId);
        if ($userInboxEntries->attachment_status === null) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0005'), 422);
        }

        if ($userInboxEntries->status == 'read' && $userInboxEntries->attachment_status == 'claimed') {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0006'), 422);
        }

        try {
            if ($userInboxEntries) {
                $customIds = $this->inboxService->customIds();

                if (in_array($userInboxEntries->inbox_messages_id, $customIds)) {
                    // 依據custom_attachments取得道具資料
                    $attachments = collect($userInboxEntries->custom_attachments ?? [])->map(function ($item) use ($userInboxEntries) {
                        return [
                            'id' => null,
                            'inbox_messages_id' => $userInboxEntries->inbox_messages_id,
                            'item_id' => $item['item_id'],
                            'amount' => $item['amount'],
                        ];
                    })->values()->toArray();

                } else {
                    // 取得附件資料
                    $attachments = $userInboxEntries->inbox?->attachments->toArray() ?? [];
                }
                if (! empty($attachments) && count($attachments) > 0) {
                    foreach ($attachments as $item) {
                        UserItemService::addItem(UserItemLogs::TYPE_SYSTEM, $user->id, $uid, $item['item_id'], $item['amount'], 1, '信件附件領取');
                    }

                }
                $entry = $this->inboxService->changeAttachmentStatus($uid, $userInboxId);
                $inbox = $this->formatInbox($entry);

                return response()->json(['data' => $inbox]);

            } else {
                return response()->json(['message' => '信件領取失敗'], 422);
            }
        } catch (\Exception $e) {
            \Log::error('信件領取失敗:', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);

            return response()->json(['message' => '信件領取失敗'], 422);
        }
    }

    // 信件刪除
    public function inboxDelete(Request $request)
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $userInboxId = $request->input('user_inbox_id');
        if (empty($userInboxId)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0001'), 422);
        }

        // 檢查信箱道具是否有領取
        $userInboxEntries = $this->inboxService->getUserInboxEntry($uid, $userInboxId);
        if (empty($userInboxEntries)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0001'), 422);
        }

        if (($userInboxEntries->status == 'read' && $userInboxEntries->attachment_status == 'unclaimed')) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0007'), 422);
        }

        $result = $this->inboxService->deleteInbox($uid, $userInboxId);
        if ($result) {
            return response()->json(['message' => '信件刪除成功']);
        } else {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0008'), 422);
        }
    }

    /** 一鍵領取所有信件附件 */
    public function inboxClaimAllAttachments()
    {
        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        $user = Users::where('uid', $uid)->first();
        if (empty($user)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0006'), 422);
        }

        $userInboxEntries = $this->inboxService->getInboxMessageWithItem($uid);
        $addFailed = false;

        foreach ($userInboxEntries as $userInboxEntry) {
            // 忽略已領取者
            if ($userInboxEntry->attachment_status !== 'unclaimed') {
                continue;
            }

            try {
                $customIds = $this->inboxService->customIds();
                // 處理附件資料（含 customIds 的 custom_attachments）
                if (in_array($userInboxEntry->inbox_messages_id, $customIds)) {
                    $attachments = collect($userInboxEntry->custom_attachments ?? [])->map(function ($item) {
                        return (object) [
                            'item_id' => $item['item_id'],
                            'amount' => $item['amount'],
                        ];
                    });
                } else {
                    $attachments = $userInboxEntry->inbox?->attachments ?? collect();
                }

                foreach ($attachments as $item) {
                    $addItem = UserItemService::addItem(UserItemLogs::TYPE_SYSTEM, $user->id, $uid, $item->item_id, $item->amount, 1, '信件附件一鍵領取');

                    if (! ($addItem['success'] ?? false)) {
                        $addFailed = true;
                        \Log::error('信件附件領取失敗', ['error_code' => $addItem['error_code'] ?? 'unknown']);
                    }
                }
                $result = $this->inboxService->changeAttachmentStatus($uid, $userInboxEntry->id);
                if (empty($result)) {
                    $addFailed = true;
                    \Log::error('信件附件領取失敗', ['error_code' => 'INBOX:0001']);
                }
            } catch (\Exception $e) {
                $addFailed = true;
                \Log::error('信件一鍵領取失敗', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        if ($addFailed) {
            return response()->json(['message' => '信件一鍵領取失敗'], 422);
        }

        return response()->json(['message' => '信件一鍵領取成功'], 200);
    }

    public function reset_inbox(Request $request)
    {
        if (! config('services.API_URL') == 'https://project_ai.jengi.tw/api' || ! config('services.API_URL') == 'https://localhost/api' || config('services.API_URL') == 'https://laravel.test/api') {
            return response()->json(['message' => '限制測試環境使用']);
        }

        $uid = auth()->guard('api')->user()->uid;
        if (empty($uid)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'AUTH:0005'), 422);
        }

        // 強制刪除uid的信件
        UserInboxEntries::where('uid', $uid)->forceDelete();

        return response()->json(['message' => '信件重置成功']);
    }

    // 取得所有信件
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $inboxMessages = InboxMessages::with('attachments', 'targets');

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
                        $inboxMessages = $inboxMessages->where($field, $value);
                    }
                    break;
                case 'status':
                    $inboxMessages = $inboxMessages->whereIn($field, explode('_', $value));
                    break;
                case 'pay_month':
                    if (! empty($value)) {
                        $pay_start = $value.'-01';
                        $pay_end = date('Y-m-t', strtotime($pay_start));
                        $inboxMessages = $inboxMessages->whereBetween('pay_date', [$pay_start, $pay_end]);
                    }
                    break;
                case 'name':
                    if (! empty($value)) {
                        $inboxMessages = $inboxMessages->where('title', 'LIKE', '%'.$value.'%');
                    }
                    break;
                default:
                    $inboxMessages = $inboxMessages->where($field, $value);
                    break;
            }
        }

        // 預設排序為 id desc
        if (empty($data['sort'])) {
            $data['sort'] = (new InboxMessages)->getTable().'.id';
            $data['direction'] = 'desc';
        }
        $sortField = $data['sort'];
        $sortDirection = ! empty($data['direction']) ? $data['direction'] : 'desc';
        $inboxMessages = $inboxMessages->orderBy($sortField, $sortDirection);

        if (empty($data['getCount'])) {
            if ($perPage == 0) {
                $inboxMessages = $inboxMessages->get();
            } else {
                $current_page = empty($data['current_page']) ? 1 : $data['current_page'];
                $inboxMessages = $inboxMessages->paginate($perPage, ['*'], 'page', $current_page);
            }
        } else {
            $inboxMessages = $inboxMessages->count();
        }

        return response()->json(['data' => $inboxMessages], 200);
    }

    // 信件統一格式化
    private function formatInbox($entry)
    {
        $attachments = [];
        $customIds = $this->inboxService->customIds();

        // 任務系統信（ID = 1）：取 custom_attachments
        if (in_array($entry->inbox_messages_id, $customIds)) {
            $attachments = collect($entry->custom_attachments ?? [])->map(function ($item) use ($entry) {
                return [
                    'id' => null,
                    'inbox_messages_id' => $entry->inbox_messages_id,
                    'item_id' => $item['item_id'],
                    'amount' => $item['amount'],
                ];
            })->values()->toArray();
        }
        // 一般信件：取 attachments 關聯
        elseif ($entry->inbox && $entry->inbox->attachments) {
            $attachments = collect($entry->inbox->attachments)->map(function ($item) use ($entry) {
                return [
                    'id' => $item->id,
                    'inbox_messages_id' => $entry->inbox_messages_id,
                    'item_id' => $item->item_id,
                    'amount' => $item->amount,
                ];
            })->values()->toArray();
        }

        return [
            'id' => $entry->id,
            'inbox_messages_id' => $entry->inbox_messages_id, // 新增此欄位用於排序
            'sender_type' => $entry->inbox->sender_type ?? null,
            'status' => $this->formatStatus($entry->status),
            'title' => $entry->inbox->title ?? null,
            'content' => $entry->inbox->content ?? null,
            'start_at' => $entry->inbox->start_at ?? null,
            'end_at' => $entry->inbox->end_at ?? null,
            'expire_at' => $entry->inbox->expire_at ?? null,
            'created_at' => $entry->created_at, // 新增此欄位用於排序
            'attachment_status' => $this->formatStatus($entry->attachment_status),
            'attachments' => $attachments,
        ];
    }

    private function formatStatus($status)
    {
        return match ($status) {
            'read' => 1,
            'claimed' => 1,
            'deleted' => 2,
            null => '-1',
            default => 0,
        };
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            $data = $request->all();

            if (empty($data['title']) || empty($data['content'])) {
                return response()->json(['message' => '標題和內容為必填欄位'], 422);
            }

            $rewardData = json_decode($data['reward'] ?? '[]', true);
            $targetData = array_filter(array_map('intval', explode(',', str_replace(' ', '', $data['target_uid'] ?? ''))));
            $inboxData = $data;
            unset($inboxData['reward']);
            unset($inboxData['target_uid']);

            $inbox = InboxMessages::create($inboxData);

            // 處理 target 資料
            if (! empty($targetData)) {
                foreach ($targetData as $target) {
                    // 如果uid不存在則直接跳過
                    if (! Users::where('uid', $target)->exists()) {
                        continue;
                    }
                    $targetDataArr = [
                        'inbox_messages_id' => $inbox->id,
                        'target_uid' => $target,
                    ];
                    InboxTargets::create($targetDataArr);
                }
            }

            // 處理 reward 資料
            if (! empty($rewardData)) {

                // 處理獎勵資料格式為 [{"101":500},{"102":100}]
                foreach ($rewardData as $rewardItem) {
                    if (is_array($rewardItem)) {
                        foreach ($rewardItem as $rewardItemId => $rewardAmount) {
                            $rewardDataArr = [
                                'inbox_messages_id' => $inbox->id,
                                'item_id' => $rewardItemId,
                                'amount' => $rewardAmount,
                            ];
                            InboxAttachments::create($rewardDataArr);
                        }
                    }
                }
            }

            \DB::commit();

            return response()->json(['success' => true, 'message' => '儲存成功'], 200);
        } catch (\Exception $e) {
            // 如果發生異常，回滾事務
            \DB::rollBack();
            \Log::error('信件儲存失敗:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['message' => '儲存失敗：'.$e->getMessage()], 500);
        }

    }

    public function update(Request $request, $id)
    {
        if ($request->has('is_send') && $request->input('is_send') == true) {
            $inbox = InboxMessages::find($id);
            if (empty($inbox)) {
                return response()->json(['message' => '找不到信件'], 404);
            }
            $inbox->status = 'active';
            $inbox->save();

            return response()->json(['message' => '信件發送成功'], 200);
        }
        \DB::beginTransaction();
        try {
            $data = $request->all();

            // 檢查標題和內容
            if (empty($data['title']) || empty($data['content'])) {
                return response()->json(['message' => '標題和內容為必填欄位'], 422);
            }

            $rewardData = json_decode($data['reward'] ?? '[]', true);
            $targetData = array_filter(array_map('intval', explode(',', str_replace(' ', '', $data['target_uid'] ?? ''))));
            $inboxData = $data;
            unset($inboxData['reward']);
            unset($inboxData['target_uid']);

            // 取得原本的信件
            $inbox = InboxMessages::find($id);
            if (empty($inbox)) {
                return response()->json(['message' => '找不到信件'], 404);
            }

            // 更新信件主體
            $inbox->update($inboxData);

            // 刪除舊的 targets
            InboxTargets::where('inbox_messages_id', $inbox->id)->delete();

            // 新增 targets
            if (! empty($targetData)) {
                foreach ($targetData as $target) {
                    // 如果uid不存在則直接跳過
                    if (! Users::where('uid', $target)->exists()) {
                        continue;
                    }
                    $targetDataArr = [
                        'inbox_messages_id' => $inbox->id,
                        'target_uid' => $target,
                    ];
                    InboxTargets::create($targetDataArr);
                }
            }

            // 刪除舊的附件
            InboxAttachments::where('inbox_messages_id', $inbox->id)->delete();

            // 新增附件
            if (! empty($rewardData)) {
                // 處理獎勵資料格式為 [{"101":500},{"102":100}]
                foreach ($rewardData as $rewardItem) {
                    if (is_array($rewardItem)) {
                        foreach ($rewardItem as $rewardItemId => $rewardAmount) {
                            $rewardDataArr = [
                                'inbox_messages_id' => $inbox->id,
                                'item_id' => $rewardItemId,
                                'amount' => $rewardAmount,
                            ];
                            InboxAttachments::create($rewardDataArr);
                        }
                    }
                }
            }

            \DB::commit();

            return response()->json(['success' => true, 'message' => '更新成功'], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('信件更新失敗:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['message' => '更新失敗：'.$e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        // 尋找inbox message
        $inboxMessage = InboxMessages::find($id);
        if (empty($inboxMessage)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0001'), 422);
        }

        // 刪除附件
        $attachments = InboxAttachments::where('inbox_messages_id', $id)->get();
        if (! $attachments->isEmpty()) {
            foreach ($attachments as $attachment) {
                $attachment->forceDelete();
            }
        }

        // 刪除targets
        $targets = InboxTargets::where('inbox_messages_id', $id)->get();
        if (! empty($targets)) {
            foreach ($targets as $target) {
                // 刪除目標資料
                $target->forceDelete();
            }
        }

        $userInboxEntry = UserInboxEntries::where('inbox_messages_id', $id)->get();
        if (! $userInboxEntry->isEmpty()) {
            foreach ($userInboxEntry as $entry) {
                // 刪除使用者信件
                $entry->forceDelete();
            }
        }

        // 刪除信件
        $inboxMessage->forceDelete();

        return response()->json(['success' => true, 'message' => '信件刪除成功']);
    }

    public function show($id)
    {
        $userInboxEntry = UserInboxEntries::where('uid', $uid)->find($id);
        if (empty($userInboxEntry)) {
            return response()->json(ErrorService::errorCode(__METHOD__, 'INBOX:0001'), 422);
        }

        // 重新排版信件資訊
        $inbox = $this->formatInbox($userInboxEntry);

        return response()->json(['success' => true, 'data' => $inbox]);
    }
}
