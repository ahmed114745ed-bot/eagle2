<?php

namespace App\Http\Controllers\Dashboard\Charing;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Charge;
use App\Models\Setting;
use App\Traits\Dashboard\DashBoardTrait;

class AdminUserCharingController extends Controller
{
    use DashBoardTrait;

    public function index($type , $id)
    {
        if($type == 'id')
        {
            $data = User::with('profile')->find($id);
        }
        else{
            $data = User::with('profile')->where('uuid',$id)->first();
        }
        $data->type =$this->user_type($data->type_user);
        return $data;
    }


    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'coins' => 'required|numeric|min:0'
        ]);
        $sender = $request->user();
        $reciver  = User::find($request->id);
        $appBaseRate = \App\Services\CoinRateService::getAppBaseRate();
        $totalCoins = $request->coins;
        $baseUsd = $appBaseRate > 0 ? $totalCoins / $appBaseRate : 0;

        $userCoins = \Cache::rememberForever('user_coins', function () {
            $setting = Setting::where('key', 'user_coins')->first();
            return $setting?->value ?? 1;
        });
        $usdAmount = $userCoins > 0 ? $request->coins / $userCoins : 0;

        $data = new Charge();
        $data->charger_id = $sender->id;
        $data->charger_type =  'dash';
        $data->user_id =  $request->id;
        $data->user_type =  'app';
        $data->amount =  $totalCoins ;
        $data->usd = $usdAmount;
        $data->balance_before =  $reciver ->coins;
        
        $data->applied_coin_rate = $appBaseRate;
        $data->total_coins = $totalCoins;
        $data->transaction_type = 'admin_to_user';
        $data->rate_source = 'app';
        $data->base_usd = $baseUsd;
        $data->base_coins = $totalCoins;
        $data->bonus_coins = 0;
        $data->profit_usd = $baseUsd;
        $data->profit_coins = $totalCoins;
        
        $data->save();


        $reciver->coins +=  $request->coins;
        $reciver->update();
        return 200;
    }


    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}
