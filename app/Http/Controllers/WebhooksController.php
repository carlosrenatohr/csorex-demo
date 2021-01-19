<?php

namespace App\Http\Controllers;

use App\Models\Logs;
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
        Logs::create([
            'type' => 2,
            'raw' => $payload
        ]);
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
        Logs::create([
            'type' => 1,
            'raw' => $payload
        ]);
    }
}
