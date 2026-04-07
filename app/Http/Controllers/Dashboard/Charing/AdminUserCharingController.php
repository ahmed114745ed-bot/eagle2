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
        $totalCoins = (float) $request->coins;
        
        $calc = \App\Services\ChargeCalculationService::calculate($totalCoins, 'coins', $appBaseRate);

        $chargeService = app(\App\Tik\Services\ChargeRepoService::class);
        $chargeService->chargeTo($sender->id, $request->id, $totalCoins, 'app', 'dash', $calc);

        return 200;
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
