<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ExchangeCoinsReportResource;
use App\Tik\Services\PaymentGatewayService;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Resources\Api\V1\RecevingReportResource;
use App\Http\Resources\Api\V1\RechargeCoinsReportResource;
use App\Models\Charge;
use App\Models\CoinLog;
use App\Models\ExchangeLog;

class CoinReportController extends Controller
{
    public function __construct(private PaymentGatewayService $paymentGatewayService) {}

    public function index()
    {
        $type = request("type") ?? "givin";
        if ($type == "givin") {
            $data = $this->givinCoins();
        }elseif ($type == "receving") {
            $data = $this->receivingCoins();
        }elseif ($type == "recharge") {
            $data = $this->rechargeCoins();
        }
        return Common::apiResponse(1, '', $data, 200);
    }

    public function givinCoins()
    {
        $user = auth()->user();
        $data = ExchangeLog::where("user_id", $user->id)
        ->when(request("start_date") && request("end_date"), function ($q) {
            $q->whereDate("created_at", ">=", request("start_date"))
              ->whereDate("created_at", "<=", request("end_date"));
        })
        ->get();
        return ExchangeCoinsReportResource::collection($data);
    }

    public function receivingCoins()
    {
        $user = auth()->user();
        $data = Charge::where("user_id", $user->id)
        ->when(request("start_date") && request("end_date"), function ($q) {
            $q->whereDate("created_at", ">=", request("start_date"))
              ->whereDate("created_at", "<=", request("end_date"));
        })
        ->get();
        return RecevingReportResource::collection($data);
    }

    public function rechargeCoins()
    {
        $user = auth()->user();
        $data = CoinLog::where("user_id", $user->id)
        ->whereIn('method', ['huawei_pay', 'google_pay', 'apple_pay'])
        ->when(request("start_date") && request("end_date"), function ($q) {
            $q->whereDate("created_at", ">=", request("start_date"))
              ->whereDate("created_at", "<=", request("end_date"));
        })
        ->get();
        return RechargeCoinsReportResource::collection($data);
    }

}
