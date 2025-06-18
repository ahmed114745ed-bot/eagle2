<?php

namespace App\Services;

use App\Models\CoinLog;
use App\Models\GameWallet;
use App\Traits\User\PaymentTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayPalService
{
    use PaymentTrait;
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
    public function create(int $referenceId, $amount, $user): string
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
                'return_url'  => url("/api/paypal-success/$referenceId"),
                'cancel_url'  => url('/api/paypal-cancel'),
                'user_action' => 'PAY_NOW',
            ],
            "purchase_units" => [
                [
                    "reference_id" => $referenceId,
                    "amount"       => [
                        "currency_code" => config('paypal.currency'),
                        "value"         => number_format($amount, 2),
                    ]
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

        return $paymentLink;
    }

    /**
     * @return mixed
     */
//    public function success(Request $request)
//    {
//        sleep(29);
//        $orderId = $request->query('token');
//        info('token-'.$this->getAccessToken());
//        info('orderId-'.$orderId);
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
//        info($response);
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
        sleep(2);
        $coinLog = CoinLog::whereId($orderId)->whereMethod('paypal')->firstOrFail();

        if ($coinLog->status){
            return response('Payment successful.', 200);
        }

        return response('Payment failed.', 500);
    }

    public function callback(Request $request): JsonResponse
    {
        info('webhook-'.$request);
        $eventType = $request->get('event_type');
        if ($eventType !== 'CHECKOUT.ORDER.APPROVED') {
            return response()->json(['status' => 'ignored', 'reason' => 'Event type not processed']);
        }

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
