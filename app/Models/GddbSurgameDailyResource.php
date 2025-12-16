<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GddbSurgameDailyResource extends Model
{
    protected $table = 'gddb_surgame_daily_resource';

    public $timestamps = false;

    protected $fillable = [
        'unique_id',
        'type',
        'stage_id',
        'difficulty',
        'stage_lv',
        'first_reward',
        'reward',
    ];

    protected $casts = [
        'unique_id' => 'integer',
        'stage_id' => 'integer',
        'difficulty' => 'integer',
        'stage_lv' => 'integer',
    ];

    public function getFirstRewardAttribute($value)
    {
        return $this->parseReward($value);
    }

    public function getRewardAttribute($value)
    {
        return $this->parseReward($value);
    }

    private function parseReward($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->formatRewards($decoded);
            }

            $fixed = '['.$value.']';
            $decoded = json_decode($fixed, true) ?? [];

            return $this->formatRewards($decoded);
        }

        return $this->formatRewards($value);
    }

    private function formatRewards($rewards)
    {
        if (! is_array($rewards)) {
            return [];
        }

        // 檢查是否為單一獎勵格式 [item_id, amount]
        if (count($rewards) === 2 && ! is_array($rewards[0])) {
            return [
                [
                    'item_id' => (int) $rewards[0],
                    'amount' => (int) $rewards[1],
                ],
            ];
        }

        // 處理多個獎勵格式 [[item_id, amount], [item_id, amount], ...]
        return array_map(function ($reward) {
            if (is_array($reward) && count($reward) >= 2) {
                return [
                    'item_id' => (int) $reward[0],
                    'amount' => (int) $reward[1],
                ];
            }

            return $reward;
        }, $rewards);
    }
}
