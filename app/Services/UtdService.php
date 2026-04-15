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

    public function success($trx, Request $request): JsonResponse
    {
        $paymentStatus = $request->query('status', 'success');
        $orderId = $request->query('orderId');

        $coinLog = CoinLog::where('trx', $trx)->whereMethod('utd')->first();

        if (!$coinLog) {
            return response()->json([
                'status'  => false,
                'trx'     => $trx,
                'message' => 'Transaction not found.',
            ], 404);
        }

        return match ($paymentStatus) {
            'success' => response()->json([
                'status'  => true,
                'trx'     => $coinLog->trx,
                'message' => 'Transaction completed successfully.',
            ]),
            'failed' => response()->json([
                'status'  => false,
                'trx'     => $coinLog->trx,
                'message' => 'Transaction failed.',
            ]),
            'cancelled' => response()->json([
                'status'  => false,
                'trx'     => $coinLog->trx,
                'message' => 'Transaction cancelled by user.',
            ]),
            default => response()->json([
                'status'  => false,
                'trx'     => $coinLog->trx,
                'message' => 'Unknown payment status.',
            ]),
        };
    }
}
