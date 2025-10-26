<?php

namespace App\Services;

use App\Enums\Payments\PaymentStatus;
use App\Models\CoinLog;
use App\Traits\User\PaymentTrait;
use Illuminate\Http\JsonResponse;
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
    use PaymentTrait;

    public function __construct()
    {
        $this->baseUrl = config('codapay.base_url');
        $this->apiKey = config('codapay.api_key');
        $this->projectId = config('codapay.project_id');
        $this->country = auth()->user()?->country?->iso_numeric ?? config('codapay.country');
        info(auth()->user()?->country?->iso_numeric);

//        $this->baseUrl = 'https://airtime.codapayments.com/airtime';
//        $this->apiKey = 'live_JI4WS6k27hHslcUOcmC9SGFDiyo';
//        $this->projectId = 289;
//        $this->country = 818;
    }

    public static function redirect_if_payment_success($trx, $country)
    {
        return url("/api/codapay-success/$trx/$country");
    }

    public function initiatePayment($trx, $amount, $userId = null)
    {
        $body = $this->getBodyForCodapay($trx, $amount, $userId);
        $url = $this->baseUrl.'/api/restful/v2.0/Payment/init.json';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $body);

        // Log::info('Codapay Payment Response', [
        //     'trx' => $trx,
        //     'body' => $body,
        //     'response' => $response->json(),
        // ]);

        $json = $response->json();
        $txnId = $json['initResult']['txnId'];
        if ($txnId){
            return $this->baseUrl."/begin?type=3&txn_id=$txnId";
        }
    }

    protected function getBodyForCodapay($trx, $amount, $userId): array
    {
        return [
            'initRequest' => [
                'country'   => $this->country,
                'payType'   => 0,
                'apiKey'    => $this->apiKey,
                'projectId' => $this->projectId,
                'orderId'   => (string)$trx,
                'currency'  => 840,
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
                        ],
                        [
                            "key" => "return_url",
                            "value" => self::redirect_if_payment_success($trx, $this->country)
//                            "value" => "https://www.example.com/{transactionId}/{orderId}/return"
                        ]
                    ]
                ]
            ]
        ];
    }

    public function callback(Request $request)
    {
        $txnId = $request->input('TxnId');
        $orderId = $request->input('OrderId');
        $totalPrice = $request->input('TotalPrice');
        $resultCode = $request->input('ResultCode');
        $checksum = $request->input('Checksum');

        // \Log::info('Codapay Callback Received', $request->all());

        $secretKey = config('codapay.api_key');
        $computedChecksum = md5($txnId . $secretKey . $orderId . $resultCode);

        if ($checksum !== $computedChecksum) {
            \Log::warning('Codapay checksum failed', [
                'expected' => $computedChecksum,
                'received' => $checksum,
            ]);
            return response()->json(['error' => 'Invalid checksum'], 403);
        }

        $coinLog = CoinLog::where('id', $orderId)->first();

        if (! $coinLog){
            return response()->json([
                'status'  => 'ignored',
                'trx'     =>  $txnId,
                'message' => "Failed",
            ]);
        }

        // \Log::info('Codapay callback verified', [
        //     'TxnId' => $txnId,
        //     'OrderId' => $orderId,
        //     'ResultCode' => $resultCode,
        // ]);

        if ($resultCode === "0") {
            // Log::info("✅ Codapay Payment Success", compact('orderId', 'txnId'));
            return $this->webhookPayment($orderId, method: 'codapay', newTrx: $txnId);
        } else {
            // Log::info("❌ Codapay Payment Failed", compact('orderId', 'txnId', 'resultCode'));
            $coinLog->update(['status' => PaymentStatus::CANCELED, 'trx' => $txnId]);
            return response()->json(['status'  => false, 'trx' => $txnId, 'message' => 'Transaction declined.',]);
        }
    }

    public function success($id, $country): JsonResponse
    {
        $coinLog = CoinLog::where('id', $id)->whereMethod('codapay')->firstOrFail();

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'Missing transaction ID'], 400);
        }

        $url = $this->baseUrl . '/api/restful/v2.0/Payment/inquiryPaymentResult.json';
        $body = [
            'inquiryPaymentRequest' => [
                'txnId'          => $coinLog->trx,
                'country'        => $country,
                'apiKey'         => $this->apiKey,
                'projectId'      => $this->projectId,
                'needStatusFinal'=> true,
            ],
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $body);

        $json = $response->json();
        // \Log::info('Codapay Inquiry Response', ['txnId' => $coinLog->trx, 'response' => $json]);

        $paymentResult = $json['paymentResult'] ?? null;
        $entries = $paymentResult['profile']['entry'] ?? [];

        $statusValue = null;

        foreach ($entries as $entry) {
            if ($entry['key'] === 'status') {
                $statusValue = strtolower($entry['value']);
            }
        }

        switch ($statusValue) {
            case 'success':
                $status = true;
                $message = 'Payment completed successfully!';
                break;
            case 'pending':
                $status = true;
                $message = 'pending';
                break;
            default:
                $status = false;
                $message = 'Payment failed or was cancelled.';
                break;
        }

        return response()->json([
            'status'  => $status,
            'trx'     => $coinLog->trx,
            'message' => $message,
        ]);
    }
}
