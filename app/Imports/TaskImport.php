<?php
namespace App\Imports;

use App\Models\TaskCategory;
use App\Models\Tasks;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class TaskImport implements ToCollection, WithStartRow
{
    public function collection(Collection $rows)
    {
        // 關閉關聯後清空任務設定
        foreach ($rows as $index => $row) {
            if (trim(implode('', $row->toArray())) === '') {
                return 0;
            }
            if ($row->count() < 7 || trim($row[0]) === '') {
                return;
            }
            try {
                Tasks::create([
                    'id'                => $row[1],
                    'localization_name' => $row[3],
                    'description'       => $row[0],
                    'summary'           => null,
                    'condition'         => $this->convertTaskData($row),
                    'check_id'          => $row[5] ?? null,
                    'reward'            => $this->convertTaskReward($row[7]),
                    'start_at'          => $row[8] == 0 ? null : $this->getDateString($row[8]),
                    'end_at'            => $row[9] == 0 ? null : $this->getDateString($row[9]),
                    'prev_task_id'      => $row[10] == 0 ? null : $row[10],
                    'next_task_id'      => $row[11] == 0 ? null : $row[11],
                    'is_auto_complete'  => $row[12] == 0 ? 0 : 1,
                    'is_active'         => $row[14] == 0 ? 0 : 1,
                    'repeat_type'       => $row[13] == 0 ? null : $row[13],
                    'type'              => $row[2],
                    'category_id'       => $this->getTaskTypeId($row[2]),
                    'auto_assign'       => 1,
                ]);
            } catch (\Throwable $e) {
                Log::error('插入第 ' . ($index + 2) . ' 行失敗: ' . $e->getMessage());
                throw $e;
            }
        }
    }

    /** 取得任務分類 */
    private function getTaskTypeId($type)
    {

        $taskType = Cache::remember('taskType', 5, function () {
            return TaskCategory::select('id', 'show_type')->get();
        });

        foreach ($taskType as $task) {
            $showTypes = $task->show_type;
            if (is_array($showTypes) && in_array($type, $showTypes)) {
                return $task['id'];
            }
        }
        return null;
    }

    /**轉換任務資料 */
    private function convertTaskData($row)
    {
        return [
            'action' => $row[4],
            'count'  => (int)$row[6],
        ];
    }

    /**轉換任務獎勵 */
    private function convertTaskReward($rewards)
    {
        // 如果是字串，先轉陣列
        if (is_string($rewards)) {
            $rewards = json_decode($rewards, true);
        }
        // 檢查解析結果
        if (is_array($rewards)) {
            if (isset($rewards[0]) && ! is_array($rewards[0])) {
                $rewards = [$rewards];
            }

            $result = [];

            foreach ($rewards as $reward) {
                if (is_array($reward) && count($reward) == 2) {
                    $result[] = [
                        'item_id' => $reward[0],
                        'amount'  => $reward[1],
                    ];
                }
            }

            return $result;
        }

        // 失敗 回傳空陣列
        return [];
    }

    /** 取得時間字串轉換成標準時間格式 */
    private function getDateString($value): ?string
    {
        if (empty($value) || $value === 0 || $value === '0') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $carbon = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return Carbon::instance($carbon)->format('Y-m-d H:i:s');
            }

            // 如果已經是字串格式
            return Carbon::parse($value)->format('Y-m-d H:i:s');

        } catch (\Exception $e) {
            Log::warning('Excel 日期解析失敗: ' . $e->getMessage());
            return null;
        }
    }

    public function startRow(): int
    {
        return 2; // 跳過第一列
    }
}
