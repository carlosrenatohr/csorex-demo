<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'next_ship_date',
        'last_ship_date',
        'purchase_date',
        'coupon',
        'coupon_id',
        'interval_type',
        'interval_number',
        'subtotal',
        'total',
        'tax',
        'shipping',
        'transaction_date',
        'event_time',
        'log_id'
    ];
}
