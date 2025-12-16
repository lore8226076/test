<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDeletionLog extends Model
{
    use HasFactory;

    protected $table = 'account_deletion_logs';

    protected $fillable = [
        'user_id',
        'uid',
        'email_hash',
        'email_masked',
        'deleted_at',
        'deleted_by',
        'reason',
        'has_payment',
        'orders_count',
        'violation_flag',
        'extra',
    ];
}
