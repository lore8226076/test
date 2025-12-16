<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MaterialStageImport;

use Throwable;

class MaterialStageController extends Controller
{
    // 匯入檔案
    public function import(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        MaterialStage::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        try {
            DB::transaction(function () use ($request) {
                Excel::import(new MaterialStageImport(), $request->file('file'), null, \Maatwebsite\Excel\Excel::XLSX);
            });

            $materialStages = MaterialStage::all();

            return response()->json([
                'success' => true,
                'message' => '匯入成功',
                'data'    => $materialStages,
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
