<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\TaskImport;
use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class TaskController extends Controller
{
    /**
     *
     *
     *
     *
     *
     * 任務修改資料記得清除快取
     *
     *
     *
     *
     *
     *
     */

    public function index()
    {
        return view('admin.tasks.list');
    }

    public function add()
    {
        return view('admin.tasks.add');
    }
    public function edit($id)
    {
        return view('admin.tasks.edit', compact('id'));
    }

    public function import(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Tasks::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        try {
            DB::transaction(function () use ($request) {
                Excel::import(new TaskImport(), $request->file('file'));
            });

            $tasks = Tasks::all();

            return response()->json([
                'success' => true,
                'message' => '匯入成功',
                'data'    => $tasks,
            ]);

        } catch (Throwable $e) {
            Log::error('匯入失敗', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '匯入失敗：' . $e->getMessage(),
            ]);
        }
    }
}
