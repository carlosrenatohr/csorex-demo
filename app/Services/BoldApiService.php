<?php
namespace App\Services;

use App\Handlers\BoldApiRequestHandler;
use App\Handlers\BoldApiResponseHandler;
use function GuzzleHttp\choose_handler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

class BoldApiService {

    const BOLD_API_BASE_URL = 'https://ro.boldapps.net/api/';

    protected $client;
    protected $requestHandler;
    protected $responseHandler;
    protected $shopifyDomain;

    public function __construct()
    {
        $this->shopifyDomain = env('MYSHOPIFY_DOMAIN');
        $this->requestHandler = new BoldApiRequestHandler(env('BOLD_API_PRIVATE_KEY'), env('BOLD_API_HANDLE'), env('MYSHOPIFY_DOMAIN'));
        $this->responseHandler = new BoldApiResponseHandler($this->requestHandler);

        $stack = HandlerStack::create(choose_handler());
        $stack->push(Middleware::mapRequest($this->requestHandler));
        $stack->push(Middleware::mapResponse($this->responseHandler));

        /**
         * Setup basic headers for making API requests
         *
         * See BoldApiRequestHandler for further detail on what happens
         * before each request goes out (e.g. adding the authorization
         * header)
         */
        $this->client = new Client([
            'handler' => $stack,
            'base_uri' => static::BOLD_API_BASE_URL.'third_party/',
            'headers' => [
                'cache-control' => 'no-cache',
                'content-type' => 'application/json',
            ],
        ]);
    }

    /**
     * Get products in a customer's subscription
     *
     * @param $shopifyCustomerId
     * @param $subscriptionId
     * @return bool|mixed
     */
    public function getProducts($shopifyCustomerId, $subscriptionId)
    {
        try {
            $res = $this->client->get('manage/subscription/orders/'.$subscriptionId.'/products?customer_id='.$shopifyCustomerId);
            $result = json_decode($res->getBody(), true);
        }
        catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    /**
     * Get initial data (subscriptions) for the given Shopify customer id
     *
     * @param $shopifyCustomerId
     * @return bool|mixed
     */
    public function getInitialData($shopifyCustomerId)
    {
        try {
            $res = $this->client->get('manage/subscription/initial_data?customer_id='.$shopifyCustomerId);
            $result = json_decode($res->getBody(), true);
        }
        catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    /**
     * Get subscriptions for the given Shopify customer id
     * @param $shopifyDomain
     * @param int $since_id = 0
     * @return bool|mixed
     */
    public function getSubscriptions($shopifyDomain, $since_id = 0)
    {
        try {
            $res = $this->client->get('subscriptions?shop='.$shopifyDomain.'&since_id='.$since_id);
            $result = json_decode($res->getBody(), true);
        }
        catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    /**
     * Get the Upcoming Orders for a given Subscription
     * @param $shopify_customer_id
     * @param int $order_id
     * @return bool|mixed
     */
    public function getUpcomingOrders($shopify_customer_id, $order_id) {
        try {
            $res = $this->client->get("manage/subscription/orders/{$order_id}/upcoming?customer_id={$shopify_customer_id}&shop={$this->shopifyDomain}");
            $result = json_decode($res->getBody(), true);
        } catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    public function getUpcomingProducts($shopify_customer_id, $order_id) {
        try {
            $res = $this->client->get("manage/subscription/orders/{$order_id}/upcoming_products?customer_id={$shopify_customer_id}&shop={$this->shopifyDomain}");
            $result = json_decode($res->getBody(), true);
        } catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    /**
     * Get Shipping Rates for a given Subscription Order
     * @param $shopify_customer_id
     * @param int $order_id
     * @return bool|mixed
     */
    public function getShippingRates($shopify_customer_id, $order_id) {
        try {
            $res = $this->client->get("manage/subscription/orders/{$order_id}/shipping_rates?customer_id={$shopify_customer_id}&shop={$this->shopifyDomain}");
            $result = json_decode($res->getBody(), true);
        } catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    /**
     * Get Discounts queue for a given Subscription Order
     * @param $shopify_customer_id
     * @param int $order_id
     * @return bool|mixed
     */
    public function getDiscounts($shopify_customer_id, $order_id) {
        try {
            $res = $this->client->get("manage/subscription/orders/{$order_id}/discounts?customer_id={$shopify_customer_id}&shop={$this->shopifyDomain}");
            $result = json_decode($res->getBody(), true);
        } catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    /**
     * Update next order date for a customer's subscription
     *
     * @param $shopifyCustomerId
     * @param $subscriptionId
     * @param $nextOrderDate
     * @return bool|mixed
     */
    public function updateNextOrderDate($shopifyCustomerId, $subscriptionId, $nextOrderDate)
    {
        try {
            $res = $this->client->put('manage/subscription/orders/'.$subscriptionId.'/next_ship_date?customer_id='.$shopifyCustomerId.'&shop='. $this->shopifyDomain, [
                'json' => [
                    'next_shipping_date' => $nextOrderDate,
                ]
            ]);

            $result = json_decode($res->getBody(), true);
        }
        catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    /**
     * Update order interval for a customer's subscription
     *
     * @param $shopifyCustomerId
     * @param $subscriptionId
     * @param $frequency_type
     * @param $frequency_num
     * @return bool|mixed
     */
    public function updateOrderInterval($shopifyCustomerId, $subscriptionId, $frequency_type, $frequency_num)
    {
        try {
            $res = $this->client->put('manage/subscription/orders/'.$subscriptionId.'/interval?customer_id='.$shopifyCustomerId.'&shop='. $this->shopifyDomain, [
                'json' => [
                    'frequency_type' => $frequency_type, // 1-Day; 2-Week;3-Month;5-Year
                    'frequency_num' => $frequency_num,
                ]
            ]);

            $result = json_decode($res->getBody(), true);
        }
        catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    /*
     * Update shipping method of a given subscription
     *
     * @param $shopifyCustomerId
     * @param $subscriptionId
     * @param $shipping_rate
     * @return bool|mixed
     */
    public function updateShippingMethod($shopifyCustomerId, $subscriptionId, $shipping_rate)
    {
        try {
            $res = $this->client->put('manage/subscription/orders/'. $subscriptionId.'/shipping_method?customer_id='.$shopifyCustomerId.'&shop='. $this->shopifyDomain, [
                'json' => [
                    'order_shipping_rate' => $shipping_rate
                ]
            ]);

            $result = json_decode($res->getBody(), true);
        }
        catch (ClientException $e) {
            return ['status' => $e->getResponse()->getStatusCode()];
        }

        return $result;
    }

    /*
     * Update discount code of a given subscription
     *
     * @param $shopifyCustomerId
     * @param $subscriptionId
     * @param $coupon_code
     * @return bool|mixed
     */
    public function updateDiscountCode($shopifyCustomerId, $subscriptionId, $coupon_code)
    {
        try {
            $res = $this->client->post('manage/subscription/orders/'. $subscriptionId.'/discount?customer_id='.$shopifyCustomerId.'&shop='. $this->shopifyDomain, [
                'json' => [
                    'discount_code' => $coupon_code
                ]
            ]);

            $result = json_decode($res->getBody(), true);
        }
        catch (ClientException $e) {
            return ['status' => $e->getResponse()];
        }

        return $result;
    }
}
