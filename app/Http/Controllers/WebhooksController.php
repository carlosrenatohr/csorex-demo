<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Order;
use App\Models\Subscription;
use App\Services\BoldApiService;
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

        $boldApiService = app(BoldApiService::class);
        $subscription_id = $props->data->subscription->id ?? '';
        $customer_id = $props->data->subscription->shopify_customer_id ?? '';
        $event_time = $props->event_time;
        if (empty($subscription_id) || empty($customer_id)) {
            Logs::create([
                'type' => 0,
                'raw' => 'Internal error. There was not a customer id or subscription id attached on the Order created webhook response.'
            ]);
        } else {
            $upcomingProds = $boldApiService->getUpcomingProducts($customer_id, $subscription_id);
            Logs::find($log->id)->update(['upcomingProducts' => $upcomingProds]);
            if ($upcomingProds['status'] != 200) {
                Logs::create(['type' => 0, 'raw' => "Internal error. There was an issue requesting the upcomingOrders API to Bold on the Order created webhook; subscription_id:{$subscription_id}, customer_id: {$customer_id}"]);
            } else {
                $product = $upcomingProds['data']['products'][0];
                // Check if it's a 2-month program acquired, otherwise it will be skipped.
                if ($product['product_title'] == "CSoreX 2-Month Program" || $product['id'] == 9772919 || $product['sku'] == 'FGN-02M-510-02') {
                    //
                    $exist = Order::where('subscription_id', $subscription_id)
                                    ->whereDate('event_time', '=', date('Y-m-d', strtotime($event_time)))
                                    ->first();
                    if (is_null($exist)) {
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
                            'event_time' => $props->event_time
                        ];
                        $order = Order::create($new_props);
                        // Check if it's a second order of a 2-month subscription
                        $ordersBelongSubscription = Order::where('subscription_id', $subscription_id)->count();
                        if ($ordersBelongSubscription == 1) { // Order #1, Day 0. Changes applied on the subscriptionCreated Webhook
                        } elseif ($ordersBelongSubscription == 2) { // Order #2, Day 30
                            // -- Update the interval subscription to send in the next 25 days
                            $next_shipping_date = date("Y-m-d", strtotime($event_time . ' + 25 days'));
                            $nextDate = $boldApiService->updateNextOrderDate($customer_id, $subscription_id, $next_shipping_date);
                        } elseif ($ordersBelongSubscription == 3) { // Order #3, Day 55
                            $interval = $boldApiService->updateOrderInterval($customer_id, $subscription_id, 1, 60);
                            $next_shipping_date = date("Y-m-d", strtotime($event_time . ' + 60 days'));
                            $nextDate = $boldApiService->updateNextOrderDate($customer_id, $subscription_id, $next_shipping_date);
                        } else {} // Order #4 and so on, every 60 days replenishment

                        // Orders Count
                        if ($ordersBelongSubscription > 0) {
                            Subscription::where('subscription_id', '=', (string) $subscription_id)->update(['order_count' => $ordersBelongSubscription]);
                        }
                    }
                }
            }
        }
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
        $boldApiService = app(BoldApiService::class);
        $subscription_id = $props->data->subscription->id ?? '';
        $customer_id = $props->data->subscription->shopify_customer_id ?? '';
        $event_time = $props->event_time ?? now();
        if (empty($subscription_id) || empty($customer_id)) {
            Logs::create([
                'type' => 0,
                'raw' => 'Internal error. There was not a customer id or subscription id attached on the Subscription created webhook response.'
            ]);
        } else {
            $upcomingProds = $boldApiService->getUpcomingProducts($customer_id, $subscription_id);
            Logs::find($log->id)->update(['upcomingProducts' => $upcomingProds]);
            if ($upcomingProds['status'] != 200 ) {
                Logs::create([
                    'type' => 0,
                    'raw' => "Internal error. There was an issue requesting the upcomingOrders API to Bold on the Subscription created webhook; subscription_id:{$subscription_id}, customer_id: {$customer_id}"
                ]);
            } else {
                $product = $upcomingProds['data']['products'][0];
                // Check if it's a 2-month program acquired, otherwise it will be skipped.
                if ($product['product_title'] == "CSoreX 2-Month Program" || $product['id'] == 9772919 || $product['sku'] == 'FGN-02M-510-02') {
                    //
                    // Store the order row on the DB
                    $new_props = [
                        'subscription_id' => $subscription_id,
                        'customer_id' => $customer_id,
                        'first_name' => $props->data->subscription->first_name,
                        'last_name' => $props->data->subscription->last_name,
                        'email' => $props->data->subscription->customer_email,
                        'log_id' => $log->id,
                        'event_time' => $event_time
                    ];
                    Subscription::create($new_props);
                    // -- Attach the discount code of Free Shipping
                    $code = 'FTRIAL2MTH';
                    $coupon = $boldApiService->updateDiscountCode($customer_id, $subscription_id, $code);
                    // -- Update the interval subscription to send in the next 30 days
                    $next_shipping_date = date("Y-m-d", strtotime($event_time . ' + 30 days'));
                    $nextDate = $boldApiService->updateNextOrderDate($customer_id, $subscription_id, $next_shipping_date);
                }
            }
        }
    }
}
