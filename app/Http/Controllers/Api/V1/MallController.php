<?php

namespace App\Http\Controllers\Api\V1;

use App\Classes\PaymentGateways\Fawry;
use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\OPayController;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\WareResource;
use App\Models\Coin;
use App\Models\CoinLog;
use App\Models\OVip;
use App\Models\Pack;
use App\Models\Silver;
use App\Models\SilverHestory;
use App\Models\User;
use App\Models\UserVip;
use App\Models\VipPrivilege;
use App\Models\Ware;
use App\Repositories\WareRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Public\Http\Services\UpgradeLevelServices;
use Modules\Public\Http\Services\UserCounterServices;

class MallController extends Controller
{



    public function coinList(Request $request){
        $user = $request->user ();
        $data = Coin::query ()->select ('id','usd','coin')->get ();
        return Common::apiResponse (1,(string) $user->di,$data,200);
    }

    public function buyCoins(Request $request){
        if(!$request->pay_method || !$request->coin_id) return Common::apiResponse (0,'missing param',null,422);
        $user = $request->user ();
        $coin = Coin::query ()->find ($request->coin_id);
        if(!$coin) return Common::apiResponse (0,'not found',null,404);
        $trx = rand (111111111111111111,999999999999999999);
        DB::beginTransaction ();
        try {
            $log = CoinLog::query ()->create (
                [
                    'paid_usd'=>$coin->usd,
                    'obtained_coins'=>$coin->coin,
                    'user_id'=>$user->id,
                    'method'=>$request->pay_method,
                    'trx'=>$trx,
                    'status'=>0
                ]
            );
            DB::commit ();
            $data = [
                'name'=>$coin->coin.'_coins',
                'amount'=>$coin->usd,
                'trx'=>$log->trx
            ];
            if ($request->pay_method == 'strip'){
                $strip = new \App\Classes\PaymentGateways\Stripe();
                $res = $strip->make ($data);
                return Common::apiResponse (1,'ok',$res,200);
            }elseif ($request->pay_method == 'fawry'){
                $fawry = new Fawry();
                $res = $fawry->make ($data);
                return Common::apiResponse (1,'ok',$res,200);
            }else if ($request->pay_method == 'opay') {
                $opay = new OPayController();
                return $opay->make ($data, $user);
            } else{
                return Common::apiResponse (0,'un supported payment gateway',null,400);
            }

//            return Common::apiResponse (1,'done',null,201);
        }catch (\Exception $exception){
            DB::rollBack ();
            return Common::apiResponse (0,'fail',null,400);
        }
    }


}
