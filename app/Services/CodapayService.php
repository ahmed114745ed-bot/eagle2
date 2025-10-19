<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodapayService
{
    protected $baseUrl;
    protected $apiKey;
    protected $projectId;
    protected $country;
    protected $payType;
    protected $currency;

    public function __construct()
    {
        $this->baseUrl   = 'https://sandbox.codapayments.com/airtime/api/restful/v2.0/Payment/init.json';
        $this->apiKey    = 'test_kgaDbBSnvQZwiOGYulZfX561bae';
        $this->projectId = 289;
        $this->country = 818;
        $this->payType = 338;
        $this->currency = 818;
    }

    public static function redirect_if_payment_success($trx)
    {
        return url(config('services.codapay.return_url_success', '/payment/success'));
    }

    public static function redirect_if_payment_failed($trx)
    {
        return url(config('services.codapay.return_url_failed', '/payment/failed'));
    }

    public function makePayment($trx, $amount, $userId = null)
    {
        $body = $this->getBodyForCodapay($trx, $amount, $userId);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, $body);

        Log::info('Codapay Payment Response', [
            'trx' => $trx,
            'body' => $body,
            'response' => $response->json(),
        ]);

        $json = $response->json();
        $txnId = $json['initResult']['txnId'];
        if ($txnId){
            return "https://sandbox.codapayments.com/airtime/begin?type=3&txn_id=$txnId";
        }
    }

    protected function getBodyForCodapay($trx, $amount, $userId): array
    {
        return [
            'initRequest' => [
                'country'   => $this->country,
                'payType'   => $this->payType,
                'apiKey'    => $this->apiKey,
                'projectId' => $this->projectId,
                'orderId'   => (string)$trx,
                'currency'  => $this->currency,
                'items' => [
                    [
                        'code'  => '1',
                        'price' => floatval(100),
                        'name'  => 'Order #' . $trx,
                    ]
                ],
                'profile' => [
                    'entry' => [
                        [
                            'key'   => 'user_id',
                            'value' => (string)($userId ?? 'guest'),
                        ]
                    ]
                ]
            ]
        ];
    }

    public function callback(): void
    {
        info('codapay log');
    }
}
