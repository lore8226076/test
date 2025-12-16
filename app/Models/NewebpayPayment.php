<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewEbpayPayment extends Model
{
    use HasFactory;

    protected $table = 'new_ebpay_payments';

    protected $fillable = [
        'new_ebpay_order_id',
        'method',
        'amount',
        'status',
        'trade_no',
        'merchant_order_no',
        'bank_code',
        'code_no',
        'expire_date',
        'raw_response',
        'raw_trade_info',
        'decoded_payload',
        'paid_at',
    ];

    protected $casts = [
        'paid_at'     => 'datetime',
        'expire_date' => 'date',
    ];

    // 付款資料屬於一筆訂單
    public function order()
    {
        return $this->belongsTo(NewebpayOrder::class, 'new_ebpay_order_id');
    }
}
