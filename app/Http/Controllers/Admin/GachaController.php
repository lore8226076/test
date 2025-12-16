<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GachaController extends Controller
{
    public function index()
    {
        return view('admin.gacha.list');
    }

    public function setting_to_ticket()
    {
        return view('admin.gacha.setting_to_ticket');
    }
    public function edit($id)
    {
        return view('admin.gacha.edit', compact('id'));
    }

    public function add()
    {
        return view('admin.gacha.add');
    }
}
