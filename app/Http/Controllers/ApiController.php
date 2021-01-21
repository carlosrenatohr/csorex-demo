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

    public function products() {
        $boldApiService = app(BoldApiService::class);

        $response = $boldApiService->getProducts('3311799300', '146219'); // 3311799300
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    public function upcomingOrders() {
        $boldApiService = app(BoldApiService::class);

        $shopifyCustomerId = env('shopify_customer_id'); //'3845586052'
        $response = $boldApiService->getUpcomingOrders('3311799300', '146219'); // 3311799300
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    public function upcomingProducts() {
        $boldApiService = app(BoldApiService::class);

        $shopifyCustomerId = env('shopify_customer_id'); //'3845586052'
        $response = $boldApiService->getUpcomingProducts('3311799300', '146219'); // 3311799300
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    public function shippingRates() {
        $boldApiService = app(BoldApiService::class);

        $response = $boldApiService->getShippingRates('3311799300', '146219');
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    public function discounts() {
        $boldApiService = app(BoldApiService::class);

        $response = $boldApiService->getDiscounts('3311799300', '146219');
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    public function updateNextShipDate() {
        $boldApiService = app(BoldApiService::class);
        $next_shipping_date = '2030-01-02';
        $add_days = date("Y-m-d", strtotime($next_shipping_date . ' + 30 days'));
        $response = $boldApiService->updateNextOrderDate('3311799300', '146219', $next_shipping_date); // original: 2036-07-16
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    public function updateOrderInterval() {
        $boldApiService = app(BoldApiService::class);
        $response = $boldApiService->updateOrderInterval('3311799300', '146219', 5, 21); // original: 2036-07-16
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    public function updateShippingMethod() {
        $boldApiService = app(BoldApiService::class);
        $shipping_rate_obj = [
            // "bold_order_id" => $subscriptionId,
            "code" => "Custom Standard Shipping (Delivers in 4-6 Business Days)",
            "name" => "Custom Standard Shipping (Delivers in 4-6 Business Days)",
            "price" => "500.01",
            "source" => "shopify",
            "need_change" => 0,
            "hash" => "5b046254c4f8e7be1775075deb50a69107ac0b16cbcf057b82de317deda42a468bfc5a6d45887c24142159f2a0093e47857c7b477857bdf4d6cc774c8ec866c5"
        ];
        $response = $boldApiService->updateShippingMethod('3311799300', '146219', $shipping_rate_obj); // original: 2036-07-16
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    public function updateDiscountCode() {
        $boldApiService = app(BoldApiService::class);
        $code = 'FTRIAL2MTH';
//        $code = 'FTRIAL2MTH-2';
        $response = $boldApiService->deleteDiscountCode('3311799300', '146219', $code);
//        $response = $boldApiService->updateDiscountCode('3311799300', '146219', $code);
        return response()->json([
            'success' => true,
            'data' => $response
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
}
