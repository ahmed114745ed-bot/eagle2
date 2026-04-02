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
            'callbackUrl' => url('/api/utd-callback'),
        ];
    }

    public function callback(Request $request)
    {
        info($request);
        info('callback');
//        die();
//        $txnId = $request->input('TxnId');
//        $orderId = $request->input('OrderId');
//        $totalPrice = $request->input('TotalPrice');
//        $resultCode = $request->input('ResultCode');
//        $checksum = $request->input('Checksum');
//
//        // \Log::info('Codapay Callback Received', $request->all());
//
//        $secretKey = config('codapay.api_key');
//        $computedChecksum = md5($txnId . $secretKey . $orderId . $resultCode);
//
//        if ($checksum !== $computedChecksum) {
//            // \Log::warning('Codapay checksum failed', [
//            //     'expected' => $computedChecksum,
//            //     'received' => $checksum,
//            // ]);
//            return response()->json(['error' => 'Invalid checksum'], 403);
//        }
//
//        $coinLog = CoinLog::where('id', $orderId)->first();
//
//        if (!$coinLog) {
//            return response()->json([
//                'status' => 'ignored',
//                'trx' => $txnId,
//                'message' => "Failed",
//            ]);
//        }
//
//        // \Log::info('Codapay callback verified', [
//        //     'TxnId' => $txnId,
//        //     'OrderId' => $orderId,
//        //     'ResultCode' => $resultCode,
//        // ]);
//
//        if ($resultCode === "0") {
//            // Log::info("✅ Codapay Payment Success", compact('orderId', 'txnId'));
//            return $this->webhookPayment($orderId, method: 'codapay', newTrx: $txnId);
//        } else {
//            // Log::info("❌ Codapay Payment Failed", compact('orderId', 'txnId', 'resultCode'));
//            $coinLog->update(['status' => PaymentStatus::CANCELED, 'trx' => $txnId]);
//            return response()->json(['status' => false, 'trx' => $txnId, 'message' => 'Transaction declined.',]);
//        }
        return response()->json(['status' => 'ok']);
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
