<?php

namespace App\Http\Controllers;

use App\Http\Services\NowPaymentsService;
use App\Models\NowpaymentOrder;
use Database\Seeders\config;
use Illuminate\Http\Request;

use Log;

use Illuminate\Support\Facades\Auth;

class NowPaymentsController extends Controller
{
    protected $nowPayments;

    public function __construct(NowPaymentsService $nowPayments)
    {
        $this->nowPayments = $nowPayments;
    }

    public function rechargeForm()
    {
        $data = $this->nowPayments->getCurrencies();

        return view('payments.now_payments.index', ['currencies' => $data['currencies']]);
    }
    public function createPayment(Request $request)
    {
        $data = [
            'price_amount' => $request->amount, // Amount in fiat currency
            'price_currency' => 'usd',
            'pay_currency' => $request->currency, // Cryptocurrency to receive
            //'ipn_callback_url' => config('services.now_payments.callback_url'), // Callback URL for IPN
            'ipn_callback_url' => 'https://eagle.utdsoftware.com/api/now-payments-callback', // Callback URL for IPN
            'order_id' => uniqid(), // Unique order ID
            'order_description' => 'Payment',
        ];

        $payment = $this->nowPayments->createPayment($data);

        if (isset($payment['payment_id'])) {
            NowpaymentOrder::create([
                'payment_id' => $payment['payment_id'],
                'pay_address' => $payment['pay_address'],
                'payment_status' => $payment['payment_status'],
                'pay_currency' => $payment['pay_currency'],
                'pay_amount' => $payment['pay_amount'],
                'amount_received' => $payment['amount_received'],
                'price_amount' => $payment['price_amount'],
                'price_currency' => $payment['price_currency'],
                'order_id' => $payment['order_id'],
                'user_id' => Auth::id() ?? 1, 
            
            ]);
        }
        return view('payments.now_payments.view',compact('payment')); // Redirect user to payment page
    }

    public function getCurrencies(){

        $response = $this->nowPayments->getCurrencies();

        $currencies = collect($response['currencies'] ?? [])->map(function ($currency) {
            return [
                'currency'    => strtoupper($currency['currency']),
                'min_amount'  => $currency['min_amount'],
                'max_amount'  => $currency['max_amount'],
            ];
        })->sortBy('currency')->values();     
        return response()->json([
            'data' => $currencies
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
        Log::info('now payments'. $request->payment_id);
        $paymentId = $request->input('payment_id');
        $status = $this->nowPayments->getPaymentStatus($paymentId);
        Log::info($status);

        // Update your database or trigger actions based on payment status
        if($status['data']['payment_status'] == 'paid'){
            NowpaymentOrder::where('payment_id', $paymentId)->update([
                'payment_status' => 'paid'
            ]);
        }
        // Example: Mark order as paid

        return response()->json(['status' => 'success']);
    }
}
