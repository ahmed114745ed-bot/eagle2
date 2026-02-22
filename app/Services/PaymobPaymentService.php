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

        $data = $this->getBodyForPaymob($trx, $amount);
        $data['paymentSubType'] = $exterData['type'];
        $data['paymentType'] = $exterData['paymentType'];
        $data['payment_method'] = $exterData['payment_method'] ?? 'card';

        if (isset($exterData['wallet_phone'])) {
            $data['wallet_phone'] = $exterData['wallet_phone'];
        }

        $utdUrl = config("services.utd_paymob.utd_url");
        $response = Http::post($utdUrl, $data);

        info($response);
        return json_decode($response);
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

        $response = Http::post($baseUrl, $data);

        return json_decode($response, true);
    }
}
