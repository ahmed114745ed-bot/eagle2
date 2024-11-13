<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Coin;
use App\Helpers\Common;
use App\Models\CoinLog;
use Illuminate\Http\Request;
use App\Tik\Services\CoinService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Classes\PaymentGateways\Fawry;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Web\OPayController;

class CoinController extends Controller
{
    public function __construct(private CoinService $coinService) {}

    public function coinList(Request $request)
    {
        $user = $request->user();
        $data = $this->coinService->coinsList();
        return Common::apiResponse(1, (string) $user->di, $data, 200);
    }

    public function buyCoins(Request $request)
    {
        if (!$request->pay_method || !$request->coin_id) return Common::apiResponse(0, 'missing param', null, 422);
        $user = $request->user();

        try {
            return  $this->coinService->buyCoins($user, $request);
        } catch (\Exception $exception) {
            DB::rollBack();
            return Common::apiResponse(0, 'fail', null, 400);
        }
    }


    public function index()
    {
        $data = $this->coinService->coinsList();
        return Common::apiResponse(1, '', $data);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'usd'         => 'required|numeric',
            'coin'         => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            $this->coinService->create($request);
            return Common::apiResponse(1, 'created successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "coin_id" => 'required|integer|exists:coins,id',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, implode(',', $validator->errors()->all()), null, 422);
        }
        $data = $this->coinService->show($request->coin_id);
        return Common::apiResponse(1, '', $data);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usd'         => 'required|numeric',
            'coin'         => 'required|numeric',
            "coin_id" => 'required|integer|exists:coins,id',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $this->coinService->update($request);
            return Common::apiResponse(1, 'updated successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function paymentCoin(Request $request)
    {
        $data =  $this->coinService->paymentCoin($request);
        return Common::apiResponse(1, '', $data);
    }
}
