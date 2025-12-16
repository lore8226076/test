<?php
namespace App\Console\Commands;

use App\Models\AccountDeletionLog;
use App\Models\Users;
use Illuminate\Console\Command;
use App\Models\UserLoginLogs;
use Illuminate\Support\Facades\Hash;

class DeleteExpiredUsers extends Command
{
    protected $signature = 'users:delete-schedule';

    protected $description = '刪除提出刪除申請超過30天的資料';

    public function handle(): void
    {


        $expiredUsers = Users::onlyTrashed()
            ->with('orders')
            ->where('deleted_at', '<', now()->startOfDay()->subDays(30))
            ->get();


        foreach ($expiredUsers as $user) {
            UserLoginLogs::where('uid', $user->uid)->delete();

            AccountDeletionLog::create([
                'user_id'        => $user->id,
                'uid'            => $user->uid,
                'email_hash'     => $user->email ? hash('sha256', strtolower(trim($user->email))) : null,
                'email_masked'   => $this->maskEmail($user->email) ?? null,
                'deleted_at'     => $user->deleted_at,
                'deleted_by'     => 'system',
                'reason'         => '使用者提出刪除申請超過30天',
                'has_payment'    => $user->orders()->exists(),
                'orders_count'   => $user->orders()->count(),
                'violation_flag' => false,
                'extra'          => json_encode([]),
            ]);

            $user->forceDelete();
        }

        $this->info("系統已完成排程刪除 {$expiredUsers->count()} 位使用者的資料，並記錄於刪除日誌中。");
    }

    private function maskEmail($email)
    {
        if (empty($email)) {
            return null;
        }

        [$name, $domain] = explode('@', $email);
        return substr($name, 0, 1) . '***@' . $domain;
    }
}
