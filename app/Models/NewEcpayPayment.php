<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewEcpayPayment extends Model
{
    protected $table = 'new_ecpay_payments';

    protected $fillable = [
        'ecpay_order_id',
        'method',
        'amount',
        'status',
        'trade_no',
        'merchant_order_no',
        'bank_code',
        'code_no',
        'expire_date',
        'paid_at',
        'raw_response',
    ];

    public function order()
    {
        return $this->belongsTo(NewEcpayOrder::class, 'ecpay_order_id');
    }
}
