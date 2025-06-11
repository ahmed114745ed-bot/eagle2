<?php

namespace App\Services;

use App\Models\GameChargeHistory;
use App\Models\GameWallet;
use App\Models\PaymentMethodHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayPalService
{
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
        \Log::info('im here');
        info($request);
        $orderId = $request->get('token');
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");
        $result = $response->json();

        $merchantRefNumber = $result['purchase_units'][0]['payments']['captures'][0]['invoice_id'] ?? null;
        $order = PaymentMethodHistory::where('utd_code', $merchantRefNumber)->first();

        if ($order) {
            $order->status = 'paid';
            $order->ref_code = $orderId;

            if ($order->type === 'game_type') {
                $this->updateDiForUser($order->amount);
                GameChargeHistory::create([
                    'value' => $order->amount,
                    'admin_id' => 0,
                ]);
            }

            $order->save();
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
