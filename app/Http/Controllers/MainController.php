<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    public function index()
    {
        return to_route('admin.dashboard.index');
    }

    public function redirectUrl()
    {
        return view('admin.dashboard.redirectUrl');
    }

    public function redirectUrlPost(Request $request)
    {
        DB::table('click_count')->insert([
            'type'       => $request->input('type'),
            'userAgent'  => $request->input('userAgent'),
            'created_at' => now(),
        ]);
        return response()->json([
            'status'  => 'success',
            'message' => 'Click recorded successfully.',
        ], 200);
    }
}
