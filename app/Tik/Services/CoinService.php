<?php

namespace App\Tik\Services;

use App\Services\FawryPaymentServiceV2;
use App\Services\FawryService;
use App\Services\PayPalService;
use App\Services\ZiniPaymentService;
use Exception;
use App\Helpers\Common;
use Illuminate\Support\Facades\DB;
use App\Services\FawryPaymentService;
use App\Classes\PaymentGateways\Fawry;
use App\Tik\Repositories\CoinRepository;
use App\Tik\Repositories\CoinLogRepository;
use App\Http\Controllers\Web\OPayController;
use App\Models\Setting;
use App\Tik\Repositories\PaymentCoinRepository;

class CoinService
{
    public function __construct(
        private readonly CoinRepository $coinRepository,
        private readonly CoinLogRepository $coinLogRepository,
        private readonly PaymentCoinRepository $paymentCoinRepository,

    ) {}

    public function coinsList()
    {
        return $this->coinRepository->allCoins();
    }
    public function coins($payment_id)
    {
        return $this->coinRepository->allCoinsByPaymentId($payment_id);
    }

    public function buyCoins($user, $request)
    {
        $coin = $this->coinRepository->findById($request->coin_id);
        if (!$coin) return Common::apiResponse(0, 'not found', null, 404);
        $trx = rand(111111111111111111, 999999999999999999);
        // DB::beginTransaction();
        try {
            $dataCoinLog = [
                'paid_usd' => $coin->usd,
                'obtained_coins' => $coin->coin,
                'user_id' => $user->id,
                'method' => $request->pay_method,
                'trx' => $trx,
                'status' => 0,
                'coin_id' => $request->coin_id,
            ];
            $log = $this->coinLogRepository->create($dataCoinLog);
            //  DB::commit();
            $data = [
                'name' => $coin->coin . '_coins',
                'amount' => $coin->usd,
                'trx' => $log->trx,
                'order_id' => $log->id,
                'user_id' => $user->id
            ];
            if ($request->pay_method == 'strip') {
                $stripe_test_secret_key = config('stripe.test_secret_key');
                $is_stripe_active = config('is_stripe_active');
                $stripe_currency = config('stripe.currency');
                $stripe_webhook_secret = config('stripe.webhook_secret');

                $data['stripe_test_secret_key'] = $stripe_test_secret_key;
                $data['is_stripe_active'] = $is_stripe_active;
                $data['stripe_currency'] = $stripe_currency;
                $data['stripe_webhook_secret'] = $stripe_webhook_secret;

                if(!$stripe_test_secret_key
                || !$is_stripe_active
                || !$stripe_currency
                || !$stripe_webhook_secret
                ){
                    return Common::apiResponse(0, __('This payment method is currently unavailable. Please choose another one.'), null, 400);
                }
                $strip = new \App\Classes\PaymentGateways\Stripe();
                $res = $strip->make($data);
                return Common::apiResponse(1, 'ok', $res, 200);
            } elseif ($request->pay_method == 'fawry') {
                $Active = config('is_fawry_active');
                if (! $Active) return Common::apiResponse(0, __('This payment method is currently unavailable. Please choose another one.'), null, 400);
                $newFawryService = new FawryPaymentServiceV2();
                $exterData = ["type" => 'charge_coin', 'paymentType' => "revenue"];

               $paymentUrl = $newFawryService->makePayment($log->id, $coin->usd, $exterData);
                if (isset($response['status']) && $paymentUrl['status']  == 0) {
                    return $paymentUrl;
                }
                return Common::apiResponse(1, 'ok', $paymentUrl, 200);
            } elseif ($request->pay_method == 'utdFawry') {
                $Active = config('is_utdFawry_active');
                if (! $Active) return Common::apiResponse(0, __('This payment method is currently unavailable. Please choose another one.'), null, 400);
                $oldFawryService = new FawryPaymentService();
                $exterData = ["type" => 'charge_coin', 'paymentType' => "expenses"];

                $paymentUrl = $oldFawryService->makePayment($log->trx, $coin->usd, $exterData);
                if (isset($response['status']) && $paymentUrl['status']  == 0) {
                    return $paymentUrl;
                }
                return Common::apiResponse(1, 'ok', $paymentUrl, 200);
            } else if ($request->pay_method == 'opay') {
                $opay = new OPayController();
                return $opay->make($data, $user);
            } else if ($request->pay_method == 'zinipay') {
                $ziniPayService = new ZiniPaymentService();
                return $ziniPayService->makePayment($log->id, $coin->usd, $user);
            } else if ($request->pay_method == 'paypal') {
                $Active = config('is_paypal_active');
                if (! $Active) return Common::apiResponse(0, __('This payment method is currently unavailable. Please choose another one.'), null, 400);
                $paypalService = new PayPalService();
                $paymentLink = $paypalService->create($log->id, $coin->usd, $user);
                return Common::apiResponse(1, 'ok', $paymentLink, 200);
            }
            else {
                return Common::apiResponse(0, 'un supported payment gateway', null, 400);
            }
        } catch (\Exception $exception) {
            //  DB::rollBack();
            return Common::apiResponse(0, 'fail', null, 400);
        }
    }

    public function show($coinId)
    {
        return $this->coinRepository->findById($coinId);
    }

    public function create($request, $payment_id)
    {
        $data = [
            'usd'         => $request->usd,
            'coin'         => $request->coin,
            'payment_gateway_id' => $payment_id,
        ];

        $this->coinRepository->create($data);
        return true;
    }

    public function update($request)
    {
        $data = [
            'usd'         => $request->usd,
            'coin'         => $request->coin,
        ];
        $this->coinRepository->update($data, $request->coin_id);
        return true;
    }

    public function paymentCoin()
    {
        return $this->paymentCoinRepository->index();
    }

    public function createPaymentCoins($request)
    {
        $image = null;
        if ($request->hasFile('photo')) {
            $image = Common::upload('images', $request->file('photo'));
        }
        $data = [
            'photo' => $image,
            'title' => $request->title,
        ];
        $this->paymentCoinRepository->create($data);
        return true;
    }

    public function updatePaymentCoins($request)
    {
        $data = [
            'title' => $request->title,
        ];
        if ($request->hasFile('photo')) {
            $data['photo'] = Common::upload('images', $request->file('photo'));
        }

        $this->paymentCoinRepository->update($data, $request->payment_coin_id);
        return true;
    }

    public function showPayment($PaymentCoinId)
    {
        return $this->paymentCoinRepository->findById($PaymentCoinId);
    }
}
