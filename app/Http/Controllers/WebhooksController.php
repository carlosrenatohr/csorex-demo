<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhooksController extends Controller
{

    //
    public function orderCreated(Request $request) {
        $data = $request->all();
        $payload = $request->getContent();
        $now = time(); // current unix timestamp
        $json = json_encode($payload, JSON_FORCE_OBJECT);
        $signature = hash_hmac('sha256', $now.'.'.$json, env('BOLD_API_SIGNING_KEY'));
        $data['headers']['X-Bold-Signature'] = $request->header('X-Bold-Signature');
        $data['headers']['timestamp'] = $request->header('timestamp');
        $equals = hash_equals($data['headers']['X-Bold-Signature'], $signature);
        $data['match'] = $equals;
        Log::error("Order Received>>" . $payload);
        $log = Logs::create([
            'type' => 2, // 1: subscription; 2:order
            'raw' => $payload
        ]);
        //
        $props = json_decode($payload);
        $new_props = [
            'subscription_id' => $props->data->subscription->id,
            'customer_id' => $props->data->subscription->shopify_customer_id,
            'first_name' => $props->data->subscription->first_name,
            'last_name' => $props->data->subscription->last_name,
            'email' => $props->data->subscription->customer_email,
            'log_id' => $log->id,
            'next_ship_date' => $props->data->subscription->next_ship_date,
            'last_ship_date' => $props->data->subscription->last_ship_date,
            'purchase_date' => $props->data->subscription->purchase_date,

            'coupon' => '-',
            'coupon_id' => $props->data->subscription->discount_code_id,
            'interval_type' => $props->data->subscription->interval_type_id,
            'interval_number' => $props->data->subscription->interval_number,

            'subtotal' => $props->data->order->subtotal,
            'total' => $props->data->order->total,
            'tax' => $props->data->order->tax,
            'shipping' => $props->data->order->shipping,
            'transaction_date' => $props->data->order->transaction_date,
            'event_time' => $props->event_time,
        ];

        $order = Order::create($new_props);
    }

    public function subscriptionCreated(Request $request) {
        $payload = $request->getContent();
        $now = time(); // current unix timestamp
        $json = json_encode($payload, JSON_FORCE_OBJECT);
        $signature = hash_hmac('sha256', $now.'.'.$json, env('BOLD_API_SIGNING_KEY'));
        $data['headers']['X-Bold-Signature'] = $request->header('X-Bold-Signature');
        $data['headers']['timestamp'] = $request->header('timestamp');
        $equals = hash_equals($data['headers']['X-Bold-Signature'], $signature);
        $data['match'] = $equals;
        Log::error("Subscription Received>>" . $payload);
        $log = Logs::create([
            'type' => 1, // 1: subscription; 2:order
            'raw' => $payload
        ]);
        //
        $props = json_decode($payload);
        $new_props = [
            'subscription_id' => $props->data->subscription->id,
            'customer_id' => $props->data->subscription->shopify_customer_id,
            'first_name' => $props->data->subscription->first_name,
            'last_name' => $props->data->subscription->last_name,
            'email' => $props->data->subscription->customer_email,
            'log_id' => $log->id,
            'event_time' => $props->event_time,
            'order_count' => 1
        ];

        $susbcription = Subscription::create($new_props);
    }
}
