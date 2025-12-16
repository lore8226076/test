<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GachaItemsImport;
use App\Models\Gachas;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\DB;
use App\Models\GachaDetails;
class GachaItemController extends Controller
{
    public function index()
    {
        return view('admin.gacha-items.list');
    }

    public function edit($id)
    {
        return view('admin.gacha-items.edit', compact('id'));
    }

    public function add()
    {
        return view('admin.gacha-items.add');
    }

     // 匯入扭蛋資料
     public function import(Request $request)
     {
        $request->validate([
            'gacha_id' => 'required|integer',
            'file' => 'required|file|mimes:xlsx',
        ]);


         $data = $request->input();
         $gacha_id = $data['gacha_id'];

         DB::beginTransaction();

         try {
             GachaDetails::where('gacha_id', $gacha_id)->delete();
             Excel::import(new GachaItemsImport($gacha_id), $request->file('file'));
         
             DB::commit();

             $gacha = Gachas::with(['gachaDetails','gachaDetails.itemDetail'])->find($gacha_id);
             return response()->json(['success' => true, 'message' => '匯入成功', 'data' => $gacha]);
         } catch (Throwable $e) {
             DB::rollBack();
         
             Log::error('匯入失敗', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

             return response()->json(['success' => false, 'message' => '匯入失敗：' . $e->getMessage()]);
         }
     }
 
}
