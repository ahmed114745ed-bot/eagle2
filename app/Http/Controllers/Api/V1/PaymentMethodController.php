<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Models\GameWallet;
use Illuminate\Http\Request;
use App\Models\GameChargeHistory;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethodHistory;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    public function callback(Request $request)
    {
        // Log::info(json_encode($request->all()));
        $callbackData = $request->all();
        $fawryRefNumber = $callbackData['fawryRefNumber'];
        $merchantRefNumber = $callbackData['merchantRefNumber'];
        $orderStatus = $callbackData['orderStatus'];

        $order = PaymentMethodHistory::where("utd_code", $merchantRefNumber)->first();
        if ($orderStatus === 'PAID') {
            $order->status = "paid";
            if ($order->type == "game_type") {
                $this->updateDiForUser($order->amount);
                GameChargeHistory::create([
                    "value" => $order->amount,
                    "admin_id" => 0,
                ]);
            }
        } elseif ($orderStatus === 'CANCELLED') {
            $order->status = "cancelled";
        } else {
            $order->status = "Error";
        }
        $order->ref_code = $fawryRefNumber;

        $order->save();

        return true;
    }

    public function updateDiForUser($amount)
    {
        $balance  = $amount * config("app.one_coins") * 2;
        $gameWallet = GameWallet::whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"))->first();
        if ($gameWallet) {
            $gameWallet->balance += $balance;
            $gameWallet->save();
        } else {
            GameWallet::create([
                'balance' => $balance,
            ]);
        }
    }

    public function store(Request $request)
    {
        $trx = PaymentMethodHistory::create([
            "amount" => $request->amount,
            "type" => 'game_type',
            "utd_code" => $request->utd_code,
        ]);

        $trxId = $trx->id;
        return Common::apiResponse(1, 'created successfully', $trxId, 200);
    }
}
