<?php
namespace App\Service;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

use App\Models\Stores;
use App\Models\StoreCustomerNotifies;
use App\Models\StoreEmployeeCategory;
use App\Service\StoreCustomerNotifyService;

use Carbon\Carbon;

class OrderService extends AppService
{
    //XGATE
    public function __construct(){

    }

    // https://smsc.xgate.com.hk/xml/checkcredit?userid=vornehk_1&password=yRoQYfyo9d6
}