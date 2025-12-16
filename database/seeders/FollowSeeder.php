<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FollowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $myUid = '1743499595';

        $uids = [
            '1738828444', '1738828450', '1738828456', '1738828461', '1738828467',
            '1738828473', '1738828479', '1738828485', '1738828491', '1738828499',
            '1738828509', '1738828525', '1738830288', '1738830405', '1738833687',
            '1738833691', '1738833765', '1738833769', '1738833781', '1738833801',
            '1738833802', '1738843709', '1738843711', '1738843716', '1738843721',
            '1738843727', '1738843732', '1738843737', '1738843742', '1738843748',
            '1738843752', '1738843754', '1738843756', '1738843759', '1738843761',
            '1738843767', '1738843771', '1738843772', '1738843775',
        ];

        $notes = [
            '超棒的作者', '很有趣', '期待新作品', '互粉一下', '厲害的人', 
            '朋友推薦', '看過作品', '偶像', '學習對象', '隨機追蹤'
        ];

        $now = Carbon::now();

        $follows = [];

        // 隨機取 25 個人「追蹤我」
        $followers = collect($uids)->shuffle()->take(25);
        foreach ($followers as $followerUid) {
            $follows[] = [
                'follower_uid' => $followerUid,
                'following_uid' => $myUid,
                'note' => fake()->randomElement($notes),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 隨機取 10 個人「我回追」
        $following = collect($uids)->shuffle()->take(10);
        foreach ($following as $followingUid) {
            $follows[] = [
                'follower_uid' => $myUid,
                'following_uid' => $followingUid,
                'note' => fake()->randomElement($notes),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 插入資料
        DB::table('follows')->insert($follows);
    }
}
