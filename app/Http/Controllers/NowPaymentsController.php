<?php

namespace App\Http\Controllers;

use App\Http\Services\NowPaymentsService;
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
            'price_amount' => 100, // Amount in fiat currency
            'price_currency' => 'usd',
            'pay_currency' => 'btc', // Cryptocurrency to receive
            'ipn_callback_url' => 'http://127.0.0.1:8000/api/now-payments-callback', // Callback URL for IPN
            'order_id' => uniqid(), // Unique order ID
            'order_description' => 'Test Payment',
        ];

        $payment = $this->nowPayments->createPayment($data);

        dd($payment);
        return redirect($payment['invoice_url']); // Redirect user to payment page
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
