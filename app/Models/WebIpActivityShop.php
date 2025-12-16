<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebIpActivityShop extends Model
{
    protected $table = 'web_ip_activity_shops';

    protected $fillable = [
        'title',
        'description',
        'detailed_description',
        'votes',
        'image_url'
    ];

    protected $casts = [
        'votes' => 'integer'
    ];

    /**
     * 增加投票數
     *
     * @param int $count 增加的票數，預設為 1
     * @return bool
     */
    public function addVotes($count = 1)
    {
        $this->votes += $count;
        return $this->save();
    }

    /**
     * 減少投票數
     *
     * @param int $count 減少的票數，預設為 1
     * @return bool
     */
    public function subtractVotes($count = 1)
    {
        $this->votes = max(0, $this->votes - $count);
        return $this->save();
    }

    /**
     * 根據投票數排序（降序）
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function orderByVotes()
    {
        return self::orderBy('votes', 'desc');
    }

    /**
     * 獲取熱門的 IP 活動（投票數 > 指定數量）
     *
     * @param int $minVotes 最小投票數，預設為 10
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPopular($minVotes = 10)
    {
        return self::where('votes', '>=', $minVotes)
                   ->orderBy('votes', 'desc')
                   ->get();
    }

    /**
     * 搜尋 IP 活動（按標題或描述）
     *
     * @param string $keyword 搜尋關鍵字
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function search($keyword)
    {
        return self::where('title', 'like', "%{$keyword}%")
                   ->orWhere('description', 'like', "%{$keyword}%")
                   ->orWhere('detailed_description', 'like', "%{$keyword}%");
    }

    /**
     * 檢查是否有圖片
     *
     * @return bool
     */
    public function hasImage()
    {
        return !empty($this->image_url);
    }
}
