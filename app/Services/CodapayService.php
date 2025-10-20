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
        $environment = env('CODAPAY_ENV', 'production');
        
        if ($environment === 'production') {
            $this->baseUrl = 'https://api.codapay.com';
        } else {
            $this->baseUrl = 'https://sandbox.codapay.com';
        }
        
        $this->apiKey = env('CODAPAY_API_KEY');
        $this->projectId = env('CODAPAY_PROJECT_ID');
        $this->country = env('CODAPAY_COUNTRY', '818');
        $this->currency = env('CODAPAY_CURRENCY', '840');
        $this->payType = env('CODAPAY_PAY_TYPE', 0);
        
        Log::info('Codapay Service Initialized', [
            'environment' => $environment,
            'base_url' => $this->baseUrl,
            'project_id' => $this->projectId,
            'country' => $this->country,
            'currency' => $this->currency,
        ]);
        
        if (!$this->baseUrl || !$this->apiKey || !$this->projectId) {
            Log::error('Codapay: Missing configuration', [
                'base_url' => $this->baseUrl,
                'has_api_key' => !empty($this->apiKey),
                'project_id' => $this->projectId,
            ]);
            throw new \Exception('Codapay configuration is incomplete');
        }
    }

    public function initiatePayment($trx, $amount, $userId = null)
    {
        try {
            $body = $this->getBodyForCodapay($trx, $amount, $userId);
            $url = $this->baseUrl . '/api/restful/v2.0/Payment/init.json';
            
            Log::info("Codapay: Initiating payment", [
                'url' => $url,
                'trx' => $trx,
                'amount' => $amount,
                'userId' => $userId,
            ]);

            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $body);

            if (!$response->successful()) {
                Log::error('Codapay: HTTP request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Codapay API request failed');
            }

            $json = $response->json();
            
            Log::info('Codapay Payment Response', [
                'trx' => $trx,
                'response' => $json,
            ]);

            return $this->handleResponse($json, $trx);

        } catch (\Exception $e) {
            Log::error('Codapay: Exception', [
                'trx' => $trx,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'حدث خطأ في معالجة الدفع'
            ];
        }
    }

    private function handleResponse($json, $trx)
    {
        $initResult = $json['initResult'] ?? [];
        $resultCode = $initResult['resultCode'] ?? null;
        $txnId = $initResult['txnId'] ?? 0;
        $resultDesc = $initResult['resultDesc'] ?? 'Unknown error';

        if ($resultCode === 0 && $txnId > 0) {
            Log::info('Codapay: Payment initialized successfully', [
                'trx' => $trx,
                'txnId' => $txnId
            ]);
            
            return [
                'success' => true,
                'payment_url' => $this->baseUrl . "/begin?type=3&txn_id={$txnId}",
                'txnId' => $txnId,
                'trx' => $trx,
            ];
        }

        Log::warning('Codapay: Payment initialization failed', [
            'trx' => $trx,
            'code' => $resultCode,
            'desc' => $resultDesc,
            'txnId' => $txnId,
        ]);

        $errorMessages = [
            201 => 'معلومات الدفع غير صحيحة',
            202 => 'فشل التحقق من البيانات',
            203 => 'الخدمة غير متاحة مؤقتاً',
            204 => 'انتهت صلاحية الطلب',
            205 => 'تم إلغاء العملية أو Account غير مفعّل',
            206 => 'طريقة الدفع غير مدعومة',
        ];

        return [
            'success' => false,
            'error_code' => $resultCode,
            'message' => $errorMessages[$resultCode] ?? $resultDesc,
            'trx' => $trx,
        ];
    }



    public static function redirect_if_payment_success()
    {
        return url('/api/codapay-success');
    }

    public static function redirect_if_payment_failed()
    {
        return url('/api/codapay-success');
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
                'returnUrl' => self::redirect_if_payment_success(),
                'failUrl'   => self::redirect_if_payment_failed(),
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
        $txnId = $request->input('TxnId');
        $orderId = $request->input('OrderId');
        $totalPrice = $request->input('TotalPrice');
        $resultCode = $request->input('ResultCode');
        $checksum = $request->input('Checksum');

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

        $coinLog = CoinLog::where('trx', $txnId)->first();

        if (! $coinLog){
            return response()->json([
                'status'  => 'ignored',
                'trx'     =>  $txnId,
                'message' => "Failed",
            ]);
        }

        \Log::info('Codapay callback verified', [
            'TxnId' => $txnId,
            'OrderId' => $orderId,
            'ResultCode' => $resultCode,
        ]);

        if ($resultCode === "0") {
            Log::info("✅ Codapay Payment Success", compact('orderId', 'txnId'));
            return $this->webhookPayment($orderId, method: 'codapay');
        } else {
            Log::info("❌ Codapay Payment Failed", compact('orderId', 'txnId', 'resultCode'));
            $coinLog->update(['status' => PaymentStatus::CANCELED, 'trx' => $txnId]);
            return response()->json(['status'  => false, 'trx' => $txnId, 'message' => 'Transaction declined.',]);
        }
    }

    public function success(Request $request): JsonResponse
    {
        $txnId = $request->query('txn_id');
        $coinLog = CoinLog::where('trx', $txnId)->whereMethod('paypal')->firstOrFail();

        if (!$txnId) {
            return response()->json(['status' => 'error', 'message' => 'Missing transaction ID'], 400);
        }

        $url = $this->baseUrl . '/api/restful/v2.0/Payment/inquiryPaymentResult.json';
        $body = [
            'inquiryPaymentRequest' => [
                'txnId'          => $txnId,
                'country'        => $this->country,
                'apiKey'         => $this->apiKey,
                'projectId'      => $this->projectId,
                'needStatusFinal'=> true,
            ],
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $body);

        $json = $response->json();
        \Log::info('Codapay Inquiry Response', ['txnId' => $txnId, 'response' => $json]);

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

    public function failed(Request $request): JsonResponse
    {
        $txnId = $request->query('txn_id');
        $coinLog = CoinLog::where('trx', $txnId)->whereMethod('paypal')->firstOrFail();

        return response()->json(['status'  => false, 'trx' => $coinLog->trx, 'message' => 'Payment failed or was cancelled.']);
    }
}
