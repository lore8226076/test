<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InboxMessages;

class InboxMessageController extends Controller
{
    public function index() 
    {
        return view('admin.inbox.list');
    }

    
}
