<?php
namespace App\Service;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
// use App\Models\MailLogs;
use Illuminate\Support\Facades\Mail;

class MailService extends AppService
{
    public function __construct(){
    }

    //Mail發送
    public static function sendMail($to='',$data='',$subject='',$view='html',$files=[]){
        if(!$to || !$data || !$subject) return;

        $maildata = ['content'=>$data ];

        $status = Mail::send('email.'.$view, $maildata, function($message) use($to,$subject,$files){
            $message->to($to)
                    ->subject($subject);
            if(count($files)>0){
                foreach ($files as $key => $path) {
                    $message->attach($path);
                }
            }
        });
        return $status;
    }
}

