<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use App\Helpers\Common;
use App\Models\CoinLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Classes\PaymentGateways\Stripe;
use Nafezly\Payments\Classes\OpayPayment;
use Modules\Public\Http\Services\UserCounterServices;

class OPayController extends Controller
{
    public function make($data, $user) {

        $verify_route_name = config('nafezly-payments.VERIFY_ROUTE_NAME');
        $response = Http::withHeaders([
            "MerchantId"=> env('OPAY_MERCHANT_ID'),
            "authorization"=>"Bearer ".env('OPAY_PUBLIC_KEY'),
            "content-type"=>"application/json"
        ])->post('https://sandboxapi.opaycheckout.com/api/v1/international/cashier/create',[
            "amount" => [
                "currency" => env('OPAY_CURRENCY'),
                "total" => $data['amount']
            ],
            "callbackUrl" => $verify_route_name."?reference_id=".$data['trx'],
            "cancelUrl" => $verify_route_name."?reference_id=".$data['trx'],
            "country" => "EG",
            "expireAt" => 780,
            "payMethod" => "BankCard",
            "productList" => [
                [
                    "description"=>"credit",
                    "name" => "credit",
                    "price" => $data['amount'],
                    "productId" => rand(),
                    "quantity" => 1
                ]
            ],
            "reference" => $data['trx'],
            "returnUrl" => $verify_route_name."?reference_id=".$data['trx'],
            "userInfo" => [
                "userEmail" => $user->email,
                "userId" => $user->id,
                "userMobile" => $user->phone,
                "userName" => $user->name
            ]
        ]);
        $json = $response->json();
        if ($json['code'] == '00000') {
            return Common::apiResponse (1,'here is payment link:',['link' => $json['data']['cashierUrl']]);
        } else {
            logger($json['message']);
            return Common::apiResponse (0,'something wrong happened, try again later',null,400);
        }
    }

    public function verify(Request $request): \Illuminate\Http\JsonResponse
    {

        $payment = new OpayPayment();
        $result = $payment->verify($request);
        if ($result['status']) {
            $coinLog = CoinLog::query ()->where ('trx',$result['data']['reference'])->where ('status',0)->where ('method','opay')->first ();
            if (!$coinLog) return Common::apiResponse (0,'cannot find transaction',null,404);
            $user = User::query ()->find ($coinLog->user_id);
            if (!$user){
                return Common::apiResponse (0,'paid but cant found user',null,404);
            }
            $user->increment ('di',$coinLog->obtained_coins);
            $coinLog->status = 1;
            $coinLog->save();
            Common::sendOfficialMessage (@$user->id,__('congratulations'),__('your recharge success'));
            (new UserCounterServices)->eventUser($user,'official-messages');
            return Common::apiResponse (1,'successfully paid',null,200);
        } else {
            return Common::apiResponse (0,'fail',null,400);
        }
    }
}
