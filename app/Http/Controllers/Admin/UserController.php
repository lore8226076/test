<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Service\MailService;
use Carbon\Carbon;

class UserController extends AppController
{
    function list(){
        // $status = MailService::sendMail('boy0039@gmail.com',
        //     '測試發送',
        //     '[沃龍超游系統]',
        //     'html');

        return view('admin.user.list');
    }
    function edit(Request $request, $id = null){
        return view('admin.user.edit', compact('id'));
    }
}
