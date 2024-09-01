<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Coin;
use App\Helpers\Common;
use App\Models\CoinLog;
use Illuminate\Http\Request;
use App\Tik\Services\CoinService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Classes\PaymentGateways\Fawry;
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
}
