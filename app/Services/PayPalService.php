<?php

namespace App\Services;

use App\Models\CoinLog;
use App\Models\GameWallet;
use App\Traits\User\PaymentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayPalService
{
    use PaymentTrait;
   public static function redirectUrl()
   {
    return url("/admin/payment-with-method");
   }

    private function getAccessToken(): string
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
    public function complete($orderID)
    {
        $url = config('paypal.base_url') . '/v2/checkout/orders/' . $orderID . '/capture';

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ];

        $response = Http::withHeaders($headers)
            ->post($url, null);

        return json_decode($response->body());
    }

    public function callback(Request $request)
    {
        info($request);
        $orderId = $request->get('orderID');
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post(config('paypal.base_url')."/v2/checkout/orders/{$orderId}/capture");
        $result = $response->json();

        info($result);
        $coinLogId = $result['purchase_units'][0]['reference_id'] ?? null;
        info($coinLogId);

        $coinLog = CoinLog::where("id", $coinLogId)->first();

        info($coinLog);
        $user = $coinLog->user;


        if (!$coinLog || $coinLog->status == 1) {
            return response()->json(['status' => 'failed', 'reason' => 'Item not found or already processed']);
        }

        $coinLog->status = 1;
        $coinLog->save();

        if ($user) {
            $user->di += $coinLog->obtained_coins;
            $user->save();
        } else {
            return response()->json(['status' => 'failed', 'reason' => 'User not found']);
        }

        return response()->json(['status' => 'success', 'details' => $result]);
    }

    public function cancel()
    {
        return response()->json(['status' => 'cancelled']);
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
