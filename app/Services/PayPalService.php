<?php

namespace App\Services;

use App\Models\CoinLog;
use App\Models\GameWallet;
use App\Traits\User\PaymentTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\ProductionEnvironment;

class PayPalService
{
    use PaymentTrait;


    public function __construct()
{
    $clientId = config('paypal.client_id');
    $clientSecret = config('paypal.client_secret');
//    $environment = new ProductionEnvironment($clientId, $clientSecret);
    $environment = new SandboxEnvironment($clientId, $clientSecret);
    $this->client = new PayPalHttpClient($environment);
}

   public static function redirectUrl()
   {
    return url("/admin/payment-with-method");
   }

    protected function getAccessToken(): string
    {
        $headers = [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode(config('paypal.client_id') . ':' . config('paypal.client_secret'))
        ];

        $response = Http::withHeaders($headers)
            ->withBody('grant_type=client_credentials')
            ->post(config('paypal.base_url') . '/v1/oauth2/token');


        return json_decode($response->body())->access_token;
    }

    /**
     * @return string
     */
    public function create(int $referenceId, $amount, $user): array|string
    {
        $id = uuid_create();

        $headers = [
            'Content-Type'      => 'application/json',
            'Authorization'     => 'Bearer ' . $this->getAccessToken(),
            'PayPal-Request-Id' => $id,
        ];
        $body = [
            "intent"         => "CAPTURE",
            'application_context' => [
                "payment_method_preference"=> "IMMEDIATE_PAYMENT_REQUIRED",
                'return_url'  => url("/api/paypal-return/$referenceId"),
                'cancel_url'  => url('/api/paypal-cancel'),
                'user_action' => 'PAY_NOW',
//                'shipping_preference' => 'NO_SHIPPING',
//                'landing_page' => 'BILLING',
            ],
            "purchase_units" => [
                [
                    "reference_id" => $referenceId,
                    "amount"       => [
                        "currency_code" => config('paypal.currency'),
                        "value"         => number_format($amount, 2),
                    ],
                    // "shipping" => [
                    //     "address" => [
                    //         "address_line_1" => "Test Street",
                    //         "admin_area_2"   => "London",
                    //         "postal_code"    => "12345",
                    //         "country_code"   => "GB"
                    //     ]
                    //     ],
                ]
            ],
        ];

        $response = Http::withHeaders($headers)
            ->withBody(json_encode($body))
            ->post(config('paypal.base_url'). '/v2/checkout/orders');

        if (isset($response['id']) && $response['status'] == 'CREATED') {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $paymentLink = $link['href'];
                }
            }
        }

        return [$response['id'], $paymentLink];
    }



    public function createOrder($referenceId, $amount, $user)
    {
        $request = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => (string)$referenceId,
                "amount" => [
                    "currency_code" => config('paypal.currency', 'USD'),
                    "value" => number_format((float)$amount, 2, '.', '')
                ]
            ]],
            "application_context" => [
                "brand_name"            => config('app.name'),
                "landing_page"          => "BILLING",         // يحاول إظهار شاشة البطاقة
                "user_action"           => "PAY_NOW",
                "shipping_preference"   => "NO_SHIPPING",     // اختياري
                "return_url"            => url("/api/paypal-return/$referenceId"),
                "cancel_url"            => url('/api/paypal-cancel'),
            ]
        ];

        $response = $this->client->execute($request);

        return $orderId  = $response->result->id ?? null;
        $status   = $response->result->status ?? null;
        \Log::info('PayPal order created', ['order_id' => $orderId, 'status' => $status]);

        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                // أرجع الرابط كما هو، بدون أي تعديل
                \Log::info("PayPal Approve URL", ['url' => $link->href]);
                return $link->href;
            }
        }

        \Log::error('PayPal approve link not found', ['order_id' => $orderId, 'status' => $status]);
        throw new \Exception("PayPal approval link not found");
    }


    /**
     * @return mixed
     */
//    public function success(Request $request)
//    {
//        sleep(29);
//        $orderId = $request->query('token');
//        if (! $orderId) {
//            return response()->json([
//                'status'  => 'error',
//                'message' => 'Missing PayPal order id',
//            ], 422);
//        }
//
//        $url = config('paypal.base_url') . "/v2/checkout/orders/{$orderId}/capture";
//        $headers = [
//            'Content-Type'  => 'application/json',
//            'Authorization' => 'Bearer ' . $this->getAccessToken(),
//        ];
//
//        $response = Http::withHeaders($headers)->post($url, null);
//
//        if ($response->failed()) {
//            return response()->json([
//                'status'  => 'error',
//                'message' => data_get($response->json(), 'message', 'Payment capture failed'),
//                'details' => $response->json(),
//            ], $response->status());
//        }
//
//        $data = $response->json();
//
//        if (data_get($data, 'status') !== 'COMPLETED') {
//            return response()->json([
//                'status'  => 'error',
//                'message' => 'Payment not completed',
//                'details' => $data,
//            ], 409);
//        }
//
//        $referenceId = data_get($data, 'purchase_units.0.reference_id');
//        $amount      = (float) data_get($data, 'purchase_units.0.payments.captures.0.amount.value');
//
//        return response()->json([
//            'status'  => 'success',
//            'order'   => $orderId,
//            'amount'  => $amount,
//        ], 200);
//    }

    public function success($orderId): mixed
    {
        sleep(25);
        $coinLog = CoinLog::whereId($orderId)->whereMethod('paypal')->firstOrFail();

        if ($coinLog->status){
            if ($coinLog->status) {
                return response()->json(['status' => 'success', 'message' => 'Payment successful.']);
            }
        }

        return response()->json(['status' => 'failed', 'message' => 'Payment failed.',], 500);
    }

    public function cancel(): JsonResponse
    {
        return response()->json(['status' => 'failed', 'message' => 'Payment cancelled.',], 500);
    }

    public function callback(Request $request): JsonResponse
    {
        info('webhook', [$request]);
        $eventType = $request->get('event_type');
        info('event type', [$eventType]);
        if ($eventType !== 'CHECKOUT.ORDER.APPROVED') {
            return response()->json(['status' => 'ignored', 'reason' => 'Event type not processed']);
        }
        info('after event type');

        $resource = $request->get('resource');
        $coinLogId = $resource['purchase_units'][0]['reference_id'] ?? null;

        return $this->webhookPayment($coinLogId);
    }

    private function updateDiForUser($amount)
    {
        $balance = $amount * config('app.one_coins') * 2;

        $wallet = GameWallet::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->first();

        if ($wallet) {
            $wallet->balance += $balance;
            $wallet->save();
        } else {
            GameWallet::create([
                'balance' => $balance,
            ]);
        }
    }
}
