<?php

namespace App\Services;

use Illuminate\Http\Request;
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
        $this->baseUrl   = config('codapay.base_url');
        $this->apiKey    = config('codapay.api_key');
        $this->projectId = config('codapay.project_id');
        $this->country   = config('codapay.country');
        $this->payType   = config('codapay.pay_type');
        $this->currency  = config('codapay.currency');
    }

    public static function redirect_if_payment_success($trx)
    {
        return url(config('codapay.return_url_success', 'api/codapay-success'));
    }

    public static function redirect_if_payment_failed($trx)
    {
        return url(config('codapay.return_url_failed', '/api/codapay-success'));
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
            return "https://airtime.codapayments.com/airtime/begin?type=3&txn_id=$txnId";
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
                        'price' => floatval($amount),
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

    public function callback(Request $request)
    {
        $txnId      = $request->input('TxnId');
        $orderId    = $request->input('OrderId');
        $totalPrice = $request->input('TotalPrice');
        $resultCode = $request->input('ResultCode');
        $checksum   = $request->input('Checksum');

        \Log::info('Codapay Callback Received', $request->all());

        $secretKey = config('codapay.api_key');
        $computedChecksum = md5($txnId . $secretKey . $orderId . $resultCode);

        if ($checksum !== $computedChecksum) {
            \Log::warning('Codapay checksum failed', [
                'expected' => $computedChecksum,
                'received' => $checksum,
            ]);
            return response()->json(['error' => 'Invalid checksum'], 403);
        }

        \Log::info('Codapay callback verified', [
            'TxnId' => $txnId,
            'OrderId' => $orderId,
            'ResultCode' => $resultCode,
        ]);

        if ($resultCode === "0") {
            Log::info("✅ Codapay Payment Success", compact('orderId', 'txnId'));
        } else {
            Log::info("❌ Codapay Payment Failed", compact('orderId', 'txnId', 'resultCode'));
        }

        return response('OK', 200);
    }

    public function success()
    {
        return response()->json(['status' => 'success', 'message' => 'Payment completed successfully!']);
    }

    public function failed()
    {
        return response()->json(['status' => 'failed', 'message' => 'Payment failed or was cancelled.']);
    }
}
