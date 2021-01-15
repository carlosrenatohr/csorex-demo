<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhooksController extends Controller
{
    private $signingKey = 'ff174422be1893aa44c024b86aeb90d94fcff4c0daa349e700a753553fa5b364';
    //
    public function orderCreated(Request $request) {
        $data = $request->all();
        $payload = $request->getContent();
        $now = time(); // current unix timestamp
        $json = json_encode($payload, JSON_FORCE_OBJECT);
        $signature = hash_hmac('sha256', $now.'.'.$json, $this->signingKey);
        $data['headers']['X-Bold-Signature'] = $request->header('X-Bold-Signature');
        $data['headers']['timestamp'] = $request->header('timestamp');
        $equals = hash_equals($data['headers']['X-Bold-Signature'], $signature);
        $data['match'] = $equals;
        Log::error("Webhook Received>>" . $payload);
        // todo add secure lines
        // https://docs.boldapps.net/subscriptions/integration/index.html#securing-webhooks
//        return response()->json($data);
    }
}
