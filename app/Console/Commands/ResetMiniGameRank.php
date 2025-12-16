<?php
namespace App\Console\Commands;

use App\Models\InboxMessages;
use App\Models\MiniGameRanks;
use App\Models\UserInboxEntries;
use Illuminate\Console\Command;

class ResetMiniGameRank extends Command
{
    protected $signature = 'mini-game:reset-rank';

    protected $description = '重置小遊戲排行榜';

    public function handle()
    {
        // 發送信件
        $this->sendRewardInbox();
        // 重置排行榜
        $this->resetRank();
        // 簡單的 log
        $this->info('小遊戲排行榜已重置，並已發送獎勵信件給前五名玩家。');
    }

    private function sendRewardInbox()
    {
        $gameIds = MiniGameRanks::select('game_id')->groupBy('game_id')->pluck('game_id');

        foreach ($gameIds as $gameId) {

            // 取得前 5 名（含 user 資訊）
            $topRanks = MiniGameRanks::with('user')
                ->where('game_id',$gameId)
                ->orderBy('score', 'desc')
                ->take(5)
                ->get();

            // 取得獎勵
            $rewards = $this->getRewards();

            if ($topRanks->isEmpty()) {
                $this->info('未找到前五名，未發送獎勵。');
                return;
            }

            foreach ($topRanks as $index => $rank) {
                $position       = $index + 1;
                $inboxMessageId = $position + 2;

                $inboxMessage = InboxMessages::find($inboxMessageId);
                if (! $inboxMessage) {
                    $this->warn("找不到第 {$position} 名的信件（ID: {$inboxMessageId}）。");
                    continue;
                }

                $reward = $rewards[$position] ?? null;
                if (! $reward) {
                    $this->warn("第 {$position} 名沒有設定獎勵。");
                    continue;
                }

                UserInboxEntries::create([
                    'uid'                => $rank->user->uid,
                    'inbox_messages_id'  => $inboxMessageId,
                    'status'             => 'unread',
                    'custom_attachments' => $reward,
                    'attachment_status'  => 'unclaimed',
                ]);

                $this->info("已發送小遊戲{$gameId}的獎勵給 UID {$rank->user->uid}，名次：{$position}。");
            }
        }
    }

    private function resetRank()
    {
        MiniGameRanks::truncate();
    }

    private function getRewards(): array
    {
        return [
            '1' => [
                [
                    'item_id' => 101,
                    'amount'  => 10000,
                ],
            ],
            '2' => [
                [
                    'item_id' => 101,
                    'amount'  => 5000,
                ],
            ],
            '3' => [
                [
                    'item_id' => 101,
                    'amount'  => 3000,
                ],
            ],
            '4' => [
                [
                    'item_id' => 101,
                    'amount'  => 2000,
                ],
            ],
            '5' => [
                [
                    'item_id' => 101,
                    'amount'  => 1000,
                ],
            ],
        ];
    }
}
