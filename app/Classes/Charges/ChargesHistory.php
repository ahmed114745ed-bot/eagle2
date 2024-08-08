<?php

namespace App\Classes\Charges;

use App\Exceptions\NotInfCoins;
use App\Helpers\Common;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Jobs\SendCustomToZend;
use App\Models\Charge;
use App\Models\User;
use App\Repositories\Room\RoomRepoInterface;
use Illuminate\Validation\ValidationException;

class ChargesHistory
{

    public function charge_make_history($user_id,$value_before,$value_after)
    {
        $amount=($value_after - $value_before);
        $data=Charge::create([
            "charger_id"=>auth()->user()->id,
            "charger_type"=>"dash",
            "user_id"=>$user_id,
            "user_type"=>'user',
            "amount"=>$amount,
            "amount_type"=>2,
            "balance_before"=>$value_before,
        ]);
    }
}
