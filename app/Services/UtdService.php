<?php

namespace App\Services;

use App\Enums\Payments\PaymentStatus;
use App\Models\CoinLog;
use App\Models\Country;
use App\Models\Setting;
use App\Traits\User\PaymentTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UtdService
{
    protected $baseUrl;
    protected $apiKey;
    protected $projectId;
    protected $country;
    protected $currency;
    use PaymentTrait;

    public function __construct()
    {
        $this->baseUrl = config('utd.base_url');
        $this->apiKey = config('utd.api_key');
        $this->projectId = config('utd.project_id');
    }

    public static function redirect_if_payment_success($trx)
    {
//        return "https://eagle.test/api/utd-success/$trx";
        return url("/api/utd-success/$trx");
    }

    public function initiatePayment($trx, $amount, $userId = null)
    {
        $body = $this->getBodyForutd($trx, $amount, $userId);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, $body);

        $json = $response->json();

        if ($response->successful() && $json['success']) {
            return $json['payUrl'];
        }

        return $json['error'];
    }

    protected function getBodyForutd($trx, $amount, $userId): array
    {
        return [
            'apiKey' => $this->apiKey,
            'amount' => $amount,
            'currency' => 'USD',
            'userId' => "$userId" ?? (string)($user?->id ?? 'guest'),
            'userName' => $user?->name ?? 'Guest User',
            'userPhone' => $user?->phone ?? '',
            'userEmail' => $user?->email ?? '',
            'reference' => (string)$trx,
            'returnUrl' => self::redirect_if_payment_success($trx),
            'callbackUrl' => url('/api/utd-callback'),//"https://eagle.test/api/utd-callback",
        ];
    }

    public function callback(Request $request)
    {

        $payload = $request->all();

        $orderId = $payload['reference'] ?? $payload['orderId'] ?? null;

//        $orderId = $payload['orderId'] ?? $payload['reference'] ?? $payload['MerchantReference'] ?? $payload['OrderId'] ?? null;
        $event = $payload['event'] ?? null;
        $status = $payload['status'] ?? $payload['resultCode'] ?? $payload['TransactionStatus'] ?? null;
        $gateway = $payload['gateway'] ?? $payload['gatewayName'] ?? null;
        $amount = $payload['amount'] ?? $payload['amountEGP'] ?? null;
        $currency = $payload['currency'] ?? $payload['currencyCode'] ?? null;
        $reference = $payload['reference'] ?? null;


        if (!$orderId) {
            Log::warning('utd-callback missing orderId', $payload);
            return response()->json(['success' => false, 'message' => 'Missing orderId'], 200);
        }

        if (isset($payload['signature']) || isset($payload['Signature'])) {
            // TODO: Implement signature verification with shared secret if available
        }


        try {
            $response = $this->webhookPayment($orderId);

            return response()->json(['success' => true, 'orderId' => $orderId, 'updated' => true], 200);
        } catch (\Exception $ex) {
            Log::error('utd-callback error', ['orderId' => $orderId, 'error' => $ex->getMessage()]);
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
    }

    public function success($trx): JsonResponse
    {
        info($trx);
        info('success');
        return response()->json(['status' => 'ok']);

//        die();
//        $coinLog = CoinLog::where('trx', $trx)->whereMethod('utd')->firstOrFail();
//
//        if (!$trx) {
//            return response()->json(['status' => 'error', 'message' => 'Missing transaction ID'], 400);
//        }
//
//        $url = $this->baseUrl . '/api/restful/v2.0/Payment/inquiryPaymentResult.json';
//        $body = [
//            'inquiryPaymentRequest' => [
//                'txnId' => $coinLog->trx,
//                'apiKey' => $this->apiKey,
//                'projectId' => $this->projectId,
//                'needStatusFinal' => true,
//            ],
//        ];
//
//        $response = Http::withHeaders([
//            'Content-Type' => 'application/json',
//        ])->post($url, $body);
//
//        $json = $response->json();
//
//        $paymentResult = $json['paymentResult'] ?? null;
//        $entries = $paymentResult['profile']['entry'] ?? [];
//
//        $statusValue = null;
//
//        foreach ($entries as $entry) {
//            if ($entry['key'] === 'status') {
//                $statusValue = strtolower($entry['value']);
//            }
//        }
//
//        switch ($statusValue) {
//            case 'success':
//                $status = true;
//                $message = 'Payment completed successfully!';
//                break;
//            case 'pending':
//                $status = true;
//                $message = 'pending';
//                break;
//            default:
//                $status = false;
//                $message = 'Payment failed or was cancelled.';
//                break;
//        }
//
//        return response()->json([
//            'status' => $status,
//            'trx' => $coinLog->trx,
//            'message' => $message,
//        ]);
    }
}
