<?php
namespace App\Service;

use App\Models\InboxMessages;
use App\Models\UserInboxEntries;

class InboxService
{
    // 取得所有可取得信件列表
    public function getAvailableInboxMessages($uid)
    {
        $priorityIds = $this->priorityIds();

        $inboxList = $this->getInboxMessageQuery($uid)
            ->orderByRaw('CASE WHEN id IN (' . implode(',', $priorityIds ?: [0]) . ') THEN 0 ELSE 1 END')
            ->orderByRaw('CASE WHEN id IN (' . implode(',', $priorityIds ?: [0]) . ') THEN id ELSE 999999999 END')
            ->orderBy('created_at', 'desc')
            ->get();

        $inboxList->each(function ($inbox) {
            $inbox->attachment_status = $inbox->attachments->first() ? 'unclaimed' : null;
        });
        return $inboxList;
    }

    // 使用者信件寫入
    public function insertAllForUser($uid, $inboxMessages)
    {
        $messageIds = $inboxMessages->pluck('id')->all();

        // 找出已存在資料
        $existing = UserInboxEntries::withTrashed()
            ->where('uid', $uid)
            ->whereIn('inbox_messages_id', $messageIds)
            ->pluck('inbox_messages_id')
            ->all();

        // 需要插入
        $needInsert = $inboxMessages->filter(function ($msg) use ($existing) {
            return ! in_array($msg->id, $existing);
        });

        // 批次 insert
        $now        = now();
        $insertData = [];

        foreach ($needInsert as $msg) {
            $insertData[] = [
                'uid'               => $uid,
                'inbox_messages_id' => $msg->id,
                'status'            => 'unread',
                'attachment_status' => $msg->attachments->first() ? 'unclaimed' : null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        if (! empty($insertData)) {
            UserInboxEntries::insert($insertData);
        }
    }

    // 讀取信件
    public function markAsRead(string $uid, int $userInboxId)
    {
        $entry = UserInboxEntries::with('inbox', 'inbox.attachments')->where('uid', $uid)->where('id', $userInboxId)->first();

        // 更新狀態
        if ($entry->status === 'unread') {
            $entry->status = 'read';
            $entry->save();
        }

        return $entry;
    }

    /** 修改信件附件狀態 */
    public function changeAttachmentStatus(string $uid, int $userInboxId)
    {
        $entry = UserInboxEntries::with(['inbox', 'inbox.attachments'])
            ->where('uid', $uid)
            ->where('id', $userInboxId)
            ->first();

        if (empty($entry)) {
            return null;
        }

        $entry->status            = 'read';
        $entry->attachment_status = 'claimed';
        $entry->save();

        return $entry;
    }

    // 刪除信件
    public function deleteInbox(string $uid, int $userInboxId)
    {
        try {
            $entry = UserInboxEntries::where('uid', $uid)->where('id', $userInboxId)->first();
            $entry->delete();
            return true;
        } catch (\Exception $e) {
            \Log::error('信件刪除失敗:', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    // 取得可用信件query
    public function getInboxMessageQuery($uid)
    {
        return InboxMessages::with('attachments', 'targets')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', now());
            })
            ->where(function ($query) use ($uid) {
                $query->where('target_type', 'all')
                    ->orWhere(function ($subQuery) use ($uid) {
                        $subQuery->whereIn('target_type', ['single', 'batch'])
                            ->whereHas('targets', function ($tq) use ($uid) {
                                $tq->where('target_uid', $uid);
                            });
                    });
            });
    }

    // 取得角色身上有道具的信件
    public function getInboxMessageWithItem(string $uid)
    {
        $customIds = $this->customIds();
        return UserInboxEntries::with('inbox')
            ->where(function ($query) use ($customIds) {
                $query->whereHas('inbox', function ($q) use ($customIds) {
                    $q->whereHas('attachments', function ($q2) {
                        $q2->where('item_id', '!=', 0);
                    });
                })->orWhereIn('inbox_messages_id', $customIds);
            })
            ->where('attachment_status', '!=', 'claimed')
            ->where('uid', $uid)
            ->get();
    }

    // 取得用戶身上信件
    public function getUserInboxEntry($uid, $userInboxId = null)
    {
        if ($userInboxId) {
            return UserInboxEntries::with('inbox')->where('uid', $uid)->where('id', $userInboxId)->first();
        } else {
            return UserInboxEntries::with('inbox')->where('uid', $uid)->get();
        }
    }

    // 自訂信件獎勵的ids
    public function customIds()
    {
        return InboxMessages::where('sender_type', 'system')->where('status', 'active')->pluck('id')->toArray();
    }

    // 優先顯示的信件ID（由小到大排序在最前面）
    public function priorityIds()
    {
        return [31];
    }
}
