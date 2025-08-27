<?php

namespace App\Tik\Services;

use App\Services\FawryPaymentServiceV2;
use App\Services\FawryService;
use App\Services\PayPalService;
use App\Services\StripeService;
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
        public StripeService $stripeService,
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

    public function buyCoins($user, $request ,$userType)
    {
        $coin = $this->coinRepository->findById($request->coin_id);
        if (!$coin) return Common::apiResponse(0, 'not found', null, 404);
        $paymentMethod = $coin->paymentCoin->type;
        $trx = rand(111111111111111111, 999999999999999999);
        // DB::beginTransaction();
        try {
            $dataCoinLog = [
                'paid_usd' => $coin->usd,
                'obtained_coins' => $coin->coin,
                'user_id' => $user->id,
                'method' => $paymentMethod,
                'trx' => $trx,
                'status' => 0,
                'coin_id' => $request->coin_id,
                'user_type' => $userType,
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
            if ($paymentMethod == 'strip') {

                $settings = $this->getStripeSettings();
                if (!$this->validateStripeSettings($settings)) {
                    return Common::apiResponse(0, __('This payment method is currently unavailable. Please choose another one.'), null, 400);
                }
                $sessionUrl = $this->createStripePayment($settings, $data);
                return Common::apiResponse(1, 'ok', $sessionUrl, 200);

            } elseif ($paymentMethod == 'fawry') {
                $Active = config('is_fawry_active');
                if (! $Active) return Common::apiResponse(0, __('This payment method is currently unavailable. Please choose another one.'), null, 400);
                $newFawryService = new FawryPaymentServiceV2();
                $exterData = ["type" => 'charge_coin', 'paymentType' => "revenue"];

               $paymentUrl = $newFawryService->makePayment($log->id, $coin->usd, $exterData);
                if (isset($response['status']) && $paymentUrl['status']  == 0) {
                    return $paymentUrl;
                }
                return Common::apiResponse(1, 'ok', $paymentUrl, 200);
            } elseif ($paymentMethod == 'utd_fawry') {
                $Active = config('is_utd_fawry_active');
                if (! $Active) return Common::apiResponse(0, __('This payment method is currently unavailable. Please choose another one.'), null, 400);
                $oldFawryService = new FawryPaymentService();
                $exterData = ["type" => 'charge_coin', 'paymentType' => "expenses"];

                $paymentUrl = $oldFawryService->makePayment($log->trx, $coin->usd, $exterData);
                if (isset($response['status']) && $paymentUrl['status']  == 0) {
                    return $paymentUrl;
                }
                return Common::apiResponse(1, 'ok', $paymentUrl, 200);
            } else if ($paymentMethod == 'opay') {
                $opay = new OPayController();
                return $opay->make($data, $user);
            } else if ($paymentMethod == 'zinipay') {
                $ziniPayService = new ZiniPaymentService();
                return $ziniPayService->makePayment($log->id, $coin->usd, $user);
            } else if ($paymentMethod == 'paypal') {
                $Active = config('is_paypal_active');
                if (! $Active) return Common::apiResponse(0, __('This payment method is currently unavailable. Please choose another one.'), null, 400);
//                $paypalService = new PayPalService();
//                $paymentLink = $paypalService->create($log->id, $coin->usd, $user);
                // $paymentLink = $paypalService->createOrder($log->id, $coin->usd, $user);
                $bladeUrl = url("/paypal/checkout/{$log->id}");

                return Common::apiResponse(1, 'ok', $bladeUrl, 200);
            }
            else {
                return Common::apiResponse(0, 'un supported payment gateway', null, 400);
            }
        } catch (Exception $exception) {
            //  DB::rollBack();
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
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

    public function paymentCoin($type)
    {
        return $this->paymentCoinRepository->index($type);
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

    public function getUserReport()
    {
        return $this->coinLogRepository->getUserCoinLogs();
    }

    public function getShippingAgencyReport($id)
    {
        return $this->coinLogRepository->getShippingAgencyCoinLogs($id);
    }


    private function getStripeSettings(): array
    {
        return [
            'secret_key'      => Setting::where('key', 'stripe_test_secret_key')->first()?->value,
            'cancel_url'      => Setting::where('key', 'stripe_cancel_url')->first()?->value,
            'success_url'     => Setting::where('key', 'stripe_success_url')->first()?->value,
            'currency'        => Setting::where('key', 'stripe_currency')->first()?->value,
            'is_active'       => Setting::where('key', 'is_strip_active')->first()?->value,
            'webhook_secret'  => Setting::where('key', 'stripe_webhook_secret')->first()?->value,
        ];
    }


    private function validateStripeSettings(array $settings): bool
    {
        return !(
            empty($settings['secret_key']) ||
            empty($settings['is_active']) ||
            empty($settings['currency']) ||
            empty($settings['webhook_secret'])
        );
    }


    private function createStripePayment(array $settings, $request)
    {

        return $this->stripeService->pay($settings['secret_key'], $request);
    }

}
