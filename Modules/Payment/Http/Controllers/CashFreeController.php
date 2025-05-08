<?php

namespace Modules\Payment\Http\Controllers;

use App\Helpers\Common;
use App\Models\Coin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Entities\UserCoinPayment;
use Modules\Payment\Enums\PaymentStatus;


class CashFreeController extends Controller
{


    public function webhook(Request $request)
    {
        try {
            $order = @$request['data']['order'];
            $payment = @$request['data']['payment'];
            Log::info('this is body $order ' . json_encode($order));
            Log::info('this is body $payment ' . json_encode($payment));
            Log::info('this is body $payment status ' . json_encode(@$payment['payment_status']));
            if ($order && $payment) {

                $orderId = $order['order_id'];
                $userCoinPayment = UserCoinPayment::query()->where('reference_id', $orderId)->orderByDesc('id')->first();

                if ($userCoinPayment && (strtolower($payment['payment_status']) == PaymentStatus::SUCCESS) && $userCoinPayment->status != PaymentStatus::SUCCESS) {
                    //            $referId = $reference_id;
                    $user = $userCoinPayment->user;
                    $coin = $userCoinPayment->coin;

                    $user->di += ($coin->coin ?? 0);
                    $user->save();
                    $userCoinPayment->status = PaymentStatus::SUCCESS;
                    $userCoinPayment->order_no = $payment['cf_payment_id'];
                    $userCoinPayment->save();


                }
            }
        } catch (Exception $e) {
            Log::info($e->getMessage());

        }
        return response()->json();
    }

    public function store(Request $request)
    {

        $user = Auth::user();
        $idPackage = $request->coins_id;

        $dollarToINR = Common::getConf('dollar_to_INR') ?? 35;
        $coin = Coin::query()->find($idPackage);

        if (!$coin || !$user) die();

        $mode = config('payment.cashfree.mode');
        $url = (($mode == 'test') ? "https://sandbox.cashfree.com" : 'https://api.cashfree.com') . "/pg/orders";

        Log::info('this is url '. $url);
        $headers = ["Content-Type: application/json", "x-api-version: 2023-08-01", //2022-01-01, 2023-08-01
            "x-client-id: " . config('payment.cashfree.app_id'), "x-client-secret: " . config('payment.cashfree.secret_key')];


        $orderId = 'order_' . rand(1111111111, 9999999999);
        $data = json_encode(['order_id' => $orderId, 'order_amount' => $coin->usd * $dollarToINR, "order_currency" => "INR", "customer_details" => ["customer_id" => 'customer_' . rand(111111111, 999999999), "customer_name" => $user->uuid ?? '', "customer_email" => @$user->email ?? '', "customer_phone" => @$user->phone ?? "9999999999",], "order_meta" => ["return_url" => route('cashfree.status', ['orderId' => $orderId])]]);

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($resp);

        UserCoinPayment::query()->create(['reference_id' => $orderId, 'user_id' => $user->id, 'coin_id' => $coin->id,]);

        return Common::apiResponse(true, 'Success', $data);
//        return response()->json($data/*->payment_link*/);
    }

    public function orderStatus(Request $request)
    {
        $orderId = $request->ordre_id;
        return view('payment::cashfree.success');
    }
}
