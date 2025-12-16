<?php
namespace App\Models;

use App\Models\Users as User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPayOrders extends Model
{
    use HasFactory;
    protected $table = 'user_pay_orders';

    // 白名單
    protected $fillable = [
        'user_id',
        'uid',
        'order_id',
        'package_id',
        'transaction_id',
        'amount',
        'payment_method',
        'status',
        'currency',
        'purchase_time',
        'acknowledged_at',
        'raw_response',
        'purchase_token',
        'error_message',
        'error_info',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
