<?php


namespace App\Http\Controllers;
use App\Services\BoldApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ApiController extends Controller
{
    public function home(Request $request) {
        $data = ['success?' => 'yeah'];
        $data['params'] = $request->all();
        if ($request->has('code')) {
            $data['code'] = $request->get('code');
        }
        Log::error('Access BOLD API');
        return response()->json($data);
    }


    public function index() {
        /** @var BoldApiService $boldApiService */
        $boldApiService = app(BoldApiService::class);

        $shopifyCustomerId = env('shopify_customer_id');

        $products = $boldApiService->getInitialData($shopifyCustomerId);

        return response()->json([
//            'success' => true,
            'data' => $products['data']
        ]);
    }

    public function subscriptions($since = 0) {
        /** @var BoldApiService $boldApiService */
        $boldApiService = app(BoldApiService::class);

        $shopifyDomain = env('MYSHOPIFY_DOMAIN');

        $subs = $boldApiService->getSubscriptions($shopifyDomain, $since);

        return response()->json([
//            'success' => true,
            'data' => $subs
        ]);
    }

    public function discounts($since = 0) {
        /** @var BoldApiService $boldApiService */
        $boldApiService = app(BoldApiService::class);

        $shopifyDomain = env('MYSHOPIFY_DOMAIN');

        $subs = $boldApiService->getDiscounts($shopifyDomain, $since);


        return response()->json([
//            'success' => true,
            'data' => $subs
        ]);
    }
}
