<?php

namespace App\Http\Controllers;

use App\Http\Services\NowPaymentsService;
use Database\Seeders\config;
use Illuminate\Http\Request;

class NowPaymentsController extends Controller
{
    protected $nowPayments;

    public function __construct(NowPaymentsService $nowPayments)
    {
        $this->nowPayments = $nowPayments;
    }

    public function createPayment(Request $request)
    {
        $data = [
            'price_amount' => $request->amount, // Amount in fiat currency
            'price_currency' => 'usd',
            'pay_currency' => $request->currency, // Cryptocurrency to receive
            'ipn_callback_url' => config('services.now_payments.callback_url'), // Callback URL for IPN
            'order_id' => uniqid(), // Unique order ID
            'order_description' => 'Payment',
        ];

        $payment = $this->nowPayments->createPayment($data);

        return response()->json([
            'payment' => $payment
        ]); // Redirect user to payment page
    }

    public function getCurrencies(){
        $data = $this->nowPayments->getCurrencies();

        return response()->json([
            'data' => $data
        ]);
    }

    public function paymentStatus($payment){
        $data = $this->nowPayments->getPaymentStatus($payment);

        return response()->json([
            'data' => $data
        ]);
    }

    public function paymentCallback(Request $request)
    {
        // Handle IPN callback from Now Payments
        $paymentId = $request->input('payment_id');
        $status = $this->nowPayments->getPaymentStatus($paymentId);

        // Update your database or trigger actions based on payment status
        // Example: Mark order as paid

        return response()->json(['status' => 'success']);
    }
}
