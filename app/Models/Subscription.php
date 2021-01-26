<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = ['subscription_id', 'customer_id', 'first_name', 'last_name', 'email', 'log_id', 'event_time', 'order_count'];
}
