<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSurGameInfo extends Model
{
    use HasFactory;

    protected $table = 'user_surgame_infos';

    protected $fillable = [
        'uid',
        'main_character_level',
        'current_exp',
        'grade_level',
        // 金幣關卡掃蕩
        'money_sweep_free_total',
        'money_sweep_free_left',
        'money_sweep_pay_total',
        'money_sweep_pay_left',
        // 經驗關卡掃蕩
        'exp_sweep_free_total',
        'exp_sweep_free_left',
        'exp_sweep_pay_total',
        'exp_sweep_pay_left',
        // 裝備關卡掃蕩
        'gift_sweep_free_total',
        'gift_sweep_free_left',
        'gift_sweep_pay_total',
        'gift_sweep_pay_left',
        // 付費掃蕩設定
        'sweep_pay_item_id',
        'sweep_pay_amount',
    ];

    protected $appends = ['main_chapter'];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'money_sweep_free_total',
        'money_sweep_free_left',
        'money_sweep_pay_total',
        'money_sweep_pay_left',
        'exp_sweep_free_total',
        'exp_sweep_free_left',
        'exp_sweep_pay_total',
        'exp_sweep_pay_left',
        'gift_sweep_free_total',
        'gift_sweep_free_left',
        'gift_sweep_pay_total',
        'gift_sweep_pay_left',
        'sweep_pay_item_id',
        'sweep_pay_amount',
    ];

    /**
     * 為新用戶創建初始遊戲資料
     */
    public static function createInitialData($uid)
    {
        return self::create([
            'uid' => $uid,
            'main_character_level' => 1,
            'current_exp' => 0,
            'grade_level' => 1,
            'money_sweep_free_total' => 2,
            'money_sweep_free_left' => 2,
            'money_sweep_pay_total' => 0,
            'money_sweep_pay_left' => 0,
            'exp_sweep_free_total' => 2,
            'exp_sweep_free_left' => 2,
            'exp_sweep_pay_total' => 0,
            'exp_sweep_pay_left' => 0,
            'gift_sweep_free_total' => 2,
            'gift_sweep_free_left' => 2,
            'gift_sweep_pay_total' => 0,
            'gift_sweep_pay_left' => 0,
            'sweep_pay_item_id' => 100,
            'sweep_pay_amount' => 20,
        ]);
    }

    public function getMainChapterAttribute()
    {
        $record = UserJourneyRecord::where('uid', $this->uid)->first();

        return $record?->current_journey_id ?? 1;
    }

    /**
     * 重置每日掃蕩次數（免費次數）
     */
    public function resetDailySweep()
    {
        $this->update([
            'money_sweep_free_left' => $this->money_sweep_free_total,
            'exp_sweep_free_left' => $this->exp_sweep_free_total,
            'gift_sweep_free_left' => $this->gift_sweep_free_total,
        ]);
    }

    /**
     * 檢查是否有可用的掃蕩次數
     *
     * @param  string  $type  關卡類型 (money, exp, gift)
     * @return array
     */
    public function checkSweepAvailability(string $type)
    {
        $type = $this->mapStageTypeToSweepType($type);
        $freeLeftField = "{$type}_sweep_free_left";
        $payLeftField = "{$type}_sweep_pay_left";

        return [
            'has_free' => $this->{$freeLeftField} > 0,
            'has_pay' => $this->{$payLeftField} > 0,
            'free_left' => $this->{$freeLeftField},
            'pay_left' => $this->{$payLeftField},
        ];
    }

    /**
     * 扣除免費掃蕩次數
     *
     * @param  string  $type  關卡類型 (money, exp, gift)
     * @param  int  $count  掃蕩次數
     * @return bool
     */
    public function consumeFreeSweep(string $type, int $count = 1)
    {
        $type = $this->mapStageTypeToSweepType($type);
        $field = "{$type}_sweep_free_left";

        if ($this->{$field} < $count) {
            return false;
        }

        $this->decrement($field, $count);

        return true;
    }

    /**
     * 扣除付費掃蕩次數
     *
     * @param  string  $type  關卡類型 (money, exp, gift)
     * @param  int  $count  掃蕩次數
     * @return bool
     */
    public function consumePaySweep(string $type, int $count = 1)
    {
        $type = $this->mapStageTypeToSweepType($type);
        $field = "{$type}_sweep_pay_left";

        if ($this->{$field} < $count) {
            return false;
        }

        $this->decrement($field, $count);

        return true;
    }

    public function mapStageTypeToSweepType($type)
    {
        return match ($type) {
            'DailyMoney' => 'money',
            'DailyExp' => 'exp',
            'MusesGift' => 'gift',
            default => 'unknown',
        };
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'uid', 'uid');
    }

    public function gddbSurgameGrade()
    {
        return $this->belongsTo(GddbSurgameGrade::class, 'grade_level', 'related_level');
    }

    public function talentSessions()
    {
        return $this->hasMany(UserTalentPoolSession::class, 'uid', 'uid');
    }

    public function slotEquipments()
    {
        return $this->hasMany(UserSlotEquipment::class, 'uid', 'uid');
    }
}
