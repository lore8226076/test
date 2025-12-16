<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewEbpayOrder extends Model
{
    use HasFactory;

    protected $table = 'new_ebpay_orders';

    protected $fillable = [
        'order_no',
        'user_id',
        'amount',
        'item_desc',
        'email',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    // 一筆訂單有一筆付款資料
    public function payment()
    {
        return $this->hasOne(NewebpayPayment::class, 'new_ebpay_order_id');
    }
}
