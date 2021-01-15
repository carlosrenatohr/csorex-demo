<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhooksController extends Controller
{
    //
    public function orderCreated(Request $request) {

        $data = $request->all();
        $data['headers']['X-Bold-Signature'] = $request->header('X-Bold-Signature');
        $data['headers']['timestamp'] = $request->header('timestamp');
        // todo add secure lines
        // https://docs.boldapps.net/subscriptions/integration/index.html#securing-webhooks
        return response()->json($data);
    }
}
