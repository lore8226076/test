<?php
namespace App\Service;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use App\Models\SmsLogs;

class SmsService extends AppService
{
    //XGATE
    public function __construct($userID, $userPassword, $storeId, $senderid = null){
        $this->vendor = 'XGATE';
        $this->userId = $userID;
        $this->userPassword = $userPassword;
        $this->storeId = $storeId;
        $this->senderid = $senderid?:$storeId;

        $this->sendUrl = 'ssl://smsc.xgate.com.hk';
    }

    // https://smsc.xgate.com.hk/xml/checkcredit?userid=vornehk_1&password=yRoQYfyo9d6

    public function send($message, $cellphone){
        $result = [
            'success' => false,
            'message' => 'SMS Server Error',
        ];

        $requestData ='UserID='.$this->userId;
        $requestData .='&UserPassword='.$this->userPassword;
        $requestData .='&MessageType=TEXT';
        $requestData .='&MessageLanguage=UTF8';
        $requestData .='&Senderid='.$this->senderid;
        $requestData .='&MessageReceiver='.$cellphone;
        $requestData .='&MessageBody='.urlencode($message);
        //UserID=xgate&UserPassword=password&MessageType=TEXT&MessageLanguage=UTF8&MessageReceiver=85290000001&MessageBody=%E7%A5%9D%E4%BD%A0%E5%B9%B8%E7%A6%8F%E5%BF%AB%E6%A8%82%0D%0A

        $sock = fsockopen($this->sendUrl,443);
        if ($sock == false) {
            return $result;
        }

        $smsLog = new SmsLogs;
        $smsLog->store_id = $this->storeId;
        $smsLog->store_id = $this->storeId;
        $smsLog->vendor = $this->vendor;
        $smsLog->receiver = $cellphone;
        $smsLog->sender = $this->senderid;
        $smsLog->message = $message;

        $request = "POST /smshub/sendsms HTTP/1.1\r\n";
        $request .= "Host: smsc.xgate.com.hk\r\n";
        $request .= "Content-type: application/x-www-form-urlencoded\r\n"; // 修正內容型別
        $request .= "Content-length: " . strlen($requestData) . "\r\n\r\n";
        $request .= $requestData;

        fputs($sock, $request);
        $buf = '';
        while (!feof($sock)) {
            $buf .= fgets($sock, 128);
        }
        fclose($sock);

        if($buf){
            $smsLog->response = $buf;
            if (preg_match('/<\?xml.*<\/ShortMessageResponse>/', $buf, $matches)) {
                $xmlData = $matches[0];
                $xmlObject = simplexml_load_string($xmlData);
                $response = json_decode(json_encode($xmlObject), true);

                if(isset($response['ResponseCode']) && $response['ResponseCode']=='A000'){
                    $result['success'] = true;
                    $smsLog->status = 1;
                }
                else{
                    $result['message'] = !empty($response['ResponseMessage'])?$response['ResponseMessage']:'socket data error';

                }
                $smsLog->response = json_encode($response);
            }
            else{
                $result['message'] = 'socket data error';
            }
        }
        $smsLog->save();
        return $result;
    }
}