<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewEcpayOrder extends Model
{
    protected $table = 'new_ecpay_orders';

    protected $fillable = [
        'order_no',
        'user_id',
        'amount',
        'item_desc',
        'email',
        'status',
        'paid_at',
    ];

    public function payment()
    {
        return $this->hasOne(EcpayPayment::class, 'ecpay_order_id');
    }
}
