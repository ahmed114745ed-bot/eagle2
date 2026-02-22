<?php

namespace App\Services;

use App\Models\PaymentMethodHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobPaymentService
{
    protected $paymobUrl;

    public function __construct()
    {
        $this->paymobUrl = config('services.utd_paymob.utd_paymob_url');
    }

    public static function redirect_if_payment_success($trx)
    {
        return url(config("services.utd_paymob.utd_paymob_return_url"));
    }

    public static function redirect_if_payment_faild($trx)
    {
        return url("/admin/payment-with-method");
    }

    public function makePayment($trx, $amount, $exterData)
    {
        // Debug configuration
        $utdUrl = config("services.utd_paymob.utd_url");
        $merchantCode = config("services.utd_paymob.utd_paymob_merchant_code");
        $secret = config("services.utd_paymob.utd_paymob_secret");
        
        Log::info('Paymob Configuration Check:', [
            'utd_url' => $utdUrl,
            'merchant_code' => $merchantCode,
            'has_secret' => !empty($secret),
            'secret_length' => strlen($secret ?? '')
        ]);
        
        // Use same data structure as createPaymentLink instead of getBodyForPaymob
        $merchantCode = config("services.utd_paymob.utd_paymob_merchant_code");
        $secret = config("services.utd_paymob.utd_paymob_secret");
        $returnUrl = url(config("services.utd_paymob.utd_paymob_return_url"));
        $orderId = 'ORDER-' . $trx . '-' . time();
        $price = number_format($amount, 2, '.', '');

        $syn = $merchantCode . $orderId . "" . $returnUrl . $orderId . "1" . $price . $secret;
        $signature = hash('sha256', $syn);

        $data = [
            'returnUrl' => $returnUrl,
            'merchantCode' => $merchantCode,
            'chargeItems' => [
                [
                    'itemId' => $orderId,
                    'price' => (float) $amount,
                ]
            ],
            'signature' => $signature,
            'special_reference' => $orderId,
            'amount' => (float) $amount,
            'description' => 'Payment via makePayment - ' . $trx,
            'paymentSubType' => $exterData['type'] ?? 'game_type',
            'paymentType' => $exterData['paymentType'] ?? 'revenue',
            'payment_method' => $exterData['payment_method'] ?? 'card',
        ];

        // Add wallet phone if provided
        if (isset($exterData['wallet_phone'])) {
            $data['wallet_phone'] = $exterData['wallet_phone'];
        }

        $utdUrl = config("services.utd_paymob.utd_url");
        
        // Debug URL configuration
        Log::info('Paymob URL Configuration:', [
            'original_utd_url' => $utdUrl,
            'utd_url_empty' => empty($utdUrl),
            'utd_url_type' => gettype($utdUrl),
            'all_paymob_config' => config("services.utd_paymob")
        ]);
        
        // Ensure we have a valid URL - try alternative config keys if main one is empty
        if (empty($utdUrl)) {
            $utdUrl = config("services.utd_paymob.utd_paymob_url");
            Log::info('Trying alternative URL config:', ['alternative_url' => $utdUrl]);
        }
        
        if (empty($utdUrl)) {
            Log::error('Paymob makePayment: utd_url configuration is empty');
            return [
                'status' => 0,
                'message' => 'Payment gateway configuration error: Missing URL',
                'data' => null
            ];
        }
        
        // Fix URL to use same endpoint as createPaymentLink (which works)
        $baseUrl = preg_replace('/\/api\/.*$/', '/api/paymob-intention', $utdUrl);
        
        // If regex didn't work, construct URL manually
        if ($baseUrl === $utdUrl && !str_contains($utdUrl, '/api/paymob-intention')) {
            // Remove trailing slash and add the correct endpoint
            $baseUrl = rtrim($utdUrl, '/') . '/api/paymob-intention';
        }
        
        // Validate the final URL
        if (empty($baseUrl) || !filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            Log::error('Paymob makePayment: Invalid URL generated', [
                'original_url' => $utdUrl,
                'generated_url' => $baseUrl
            ]);
            return [
                'status' => 0,
                'message' => 'Payment gateway configuration error: Invalid URL',
                'data' => null
            ];
        }
        
        Log::info('Paymob makePayment Request:', [
            'original_url' => $utdUrl,
            'corrected_url' => $baseUrl,
            'data' => $data,
            'trx' => $trx,
            'amount' => $amount
        ]);
        
        try {
            $response = Http::timeout(30)->post($baseUrl, $data);
            
            Log::info('Paymob makePayment Response:', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body(),
                'json' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error('Paymob makePayment Exception:', [
                'error' => $e->getMessage(),
                'url' => $baseUrl,
                'data' => $data
            ]);
            return [
                'status' => 0,
                'message' => 'Connection error: ' . $e->getMessage(),
                'data' => null
            ];
        }

        // Get response data
        $responseData = $response->json();
        
        if (!$response->successful()) {
            Log::error('Paymob Payment Failed:', ['response' => $responseData]);
            return [
                'status' => 0,
                'message' => $responseData['message'] ?? 'Payment request failed',
                'data' => $responseData
            ];
        }
        
        // Check if responseData is directly a URL string
        if (is_string($responseData) && filter_var($responseData, FILTER_VALIDATE_URL)) {
            return [
                'status' => 1,
                'payment_url' => $responseData,
                'message' => 'Payment link created successfully',
                'data' => ['url' => $responseData]
            ];
        }
        
        // Check for payment URL in different possible fields (for object responses)
        $paymentUrl = null;
        if (is_array($responseData) || is_object($responseData)) {
            $responseArray = (array) $responseData;
            if (isset($responseArray['payment_url'])) {
                $paymentUrl = $responseArray['payment_url'];
            } elseif (isset($responseArray['redirectionUrl'])) {
                $paymentUrl = $responseArray['redirectionUrl'];
            } elseif (isset($responseArray['url'])) {
                $paymentUrl = $responseArray['url'];
            } elseif (isset($responseArray['checkout_url'])) {
                $paymentUrl = $responseArray['checkout_url'];
            } elseif (isset($responseArray['payment_link'])) {
                $paymentUrl = $responseArray['payment_link'];
            }
        }
        
        if ($paymentUrl && filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
            return [
                'status' => 1,
                'payment_url' => $paymentUrl,
                'message' => 'Payment link created successfully',
                'data' => $responseData
            ];
        }
        
        Log::warning('Paymob Payment: No payment URL found in response', ['response' => $responseData]);
        return [
            'status' => 1,
            'payment_url' => $responseData,
            'message' => 'Payment processed but no URL found',
            'data' => $responseData
        ];
    }

    public function getBodyForPaymob($trx, $amount)
    {
        $merchantCode = config("services.utd_paymob.utd_paymob_merchant_code");
        $merchantRefNum = $trx;
        $secure_key = config("services.utd_paymob.utd_paymob_secret");
        $price = number_format($amount, 2, '.', '');
        $qty = 1;
        $syn = $merchantCode.$merchantRefNum."".self::redirect_if_payment_success($trx).$trx.$qty.$price.$secure_key;
        $signature = hash('sha256', $syn);

        $data = [
            "merchantCode" => $merchantCode,
            "merchantRefNum" => $merchantRefNum,
            "language" => "en-gb",
            "chargeItems" => [
                [
                    "itemId" => $trx,
                    "price" => $price,
                    "quantity" => $qty,
                ]
            ],
            "returnUrl" => self::redirect_if_payment_success($trx),
            "signature" => $signature,
        ];

        return $data;
    }

    public function createPaymentLink($amount, $name, $description = '', $email = null, $phone = null, $trx = null, $expiresAt = null, $isLive = false )
    {
        PaymentMethodHistory::create([
            "amount" => $amount,
            "type" => 'game_type',
            "utd_code" => $trx
        ]);

        $utdUrl = config("services.utd_paymob.utd_url");
        $baseUrl = preg_replace('/\/api\/.*$/', '/api/paymob-intention', $utdUrl);

        $merchantCode = config("services.utd_paymob.utd_paymob_merchant_code");
        $secure_key = config("services.utd_paymob.utd_paymob_secret");
        $returnUrl = url(config("services.utd_paymob.utd_paymob_return_url"));
        $orderId = 'ORDER-' . time();
        $price = number_format($amount, 2, '.', '');

        $syn = $merchantCode . $orderId . "" . $returnUrl . $orderId . "1" . $price . $secure_key;
        $signature = hash('sha256', $syn);

        // Split name into first_name and last_name
        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? 'User';

        $data = [
            'returnUrl' => $returnUrl,
            'merchantCode' => $merchantCode,
            'chargeItems' => [
                [
                    'itemId' => $orderId,
                    'price' => (float) $amount,
                ]
            ],
            'signature' => $signature,
            'special_reference' => $orderId,
            'amount' => (float) $amount,
            'description' => $description,
            'billing_data' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone_number' => $phone,
            ],
        ];

        Log::info('Paymob createPaymentLink Request:', [
            'url' => $baseUrl,
            'data' => $data,
            'amount' => $amount,
            'trx' => $trx
        ]);

        try {
            $response = Http::timeout(30)->post($baseUrl, $data);

            Log::info('Paymob createPaymentLink Response:', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body(),
                'json' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error('Paymob createPaymentLink Exception:', [
                'error' => $e->getMessage(),
                'url' => $baseUrl,
                'data' => $data
            ]);
            return [
                'status' => 0,
                'message' => 'Connection error: ' . $e->getMessage(),
                'data' => null
            ];
        }

        // Get response data
        $responseData = $response->json();
        
        if (!$response->successful()) {
            Log::error('Paymob CreatePaymentLink Failed:', ['response' => $responseData]);
            return [
                'status' => 0,
                'message' => $responseData['message'] ?? 'Failed to create payment link',
                'data' => $responseData
            ];
        }
        
        // Check if responseData is directly a URL string
        if (is_string($responseData) && filter_var($responseData, FILTER_VALIDATE_URL)) {
            return [
                'status' => 1,
                'payment_url' => $responseData,
                'message' => 'Payment link created successfully',
                'data' => ['url' => $responseData]
            ];
        }
        
        // Check for payment URL in different possible fields (for object responses)
        $paymentUrl = null;
        if (is_array($responseData) || is_object($responseData)) {
            $responseArray = (array) $responseData;
            if (isset($responseArray['payment_url'])) {
                $paymentUrl = $responseArray['payment_url'];
            } elseif (isset($responseArray['redirectionUrl'])) {
                $paymentUrl = $responseArray['redirectionUrl'];
            } elseif (isset($responseArray['url'])) {
                $paymentUrl = $responseArray['url'];
            } elseif (isset($responseArray['checkout_url'])) {
                $paymentUrl = $responseArray['checkout_url'];
            } elseif (isset($responseArray['payment_link'])) {
                $paymentUrl = $responseArray['payment_link'];
            }
        }
        
        if ($paymentUrl && filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
            return [
                'status' => 1,
                'payment_url' => $paymentUrl,
                'message' => 'Payment link created successfully',
                'data' => $responseData
            ];
        }
        
        // If no URL found, return the whole response for debugging
        Log::warning('Paymob CreatePaymentLink: No payment URL found in response', ['response' => $responseData]);
        return [
            'status' => 1,
            'payment_url' => $responseData,
            'message' => 'Payment processed but no URL found',
            'data' => $responseData
        ];
    }
}
