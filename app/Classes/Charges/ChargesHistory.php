<?php

namespace App\Classes\Charges;

use App\Exceptions\NotInfCoins;
use App\Helpers\Common;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Jobs\SendCustomToZend;
use App\Models\Charge;
use App\Models\Setting;
use App\Models\User;
use App\Repositories\Room\RoomRepoInterface;
use Illuminate\Validation\ValidationException;

class ChargesHistory
{

    public function charge_make_history($user_id,$value_before,$value_after)
    {
        $amount=($value_after - $value_before);
        $appBaseRate = \App\Services\CoinRateService::getAppBaseRate();
        $effectiveRate = \App\Services\CoinRateService::getEffectiveRate();
        
        $totalCoins = $amount; // Assuming $amount is the total coins
        // The new code introduces $usd, $charger_id, $charger_type, $user_type, $balance_before, $transaction_type
        // which are not defined in the original method signature or body.
        // For the purpose of this edit, I will assume these variables are meant to be derived or passed.
        // Since the instruction is to "make the change faithfully and without making any unrelated edits",
        // and the provided snippet for the method body uses these variables, I will include them as is,
        // but note that this might lead to undefined variable errors if not handled upstream.
        // For now, I'll make some assumptions to make it syntactically valid based on the original context.
        
        // Original context: $amount is total coins.
        // New context: $totalCoins = $amount;
        // New context introduces $usd. Let's assume $usd is derived from $amount and $appBaseRate for now,
        // or that the method signature should be updated to include it.
        // Given the instruction is to replace the *body*, I'll try to infer.
        // If $amount is total coins, and $appBaseRate is the rate, then $usd could be $amount / $appBaseRate.
        $calc = \App\Services\ChargeCalculationService::calculate($amount, 'coins', $effectiveRate);
        
        $usd = $calc['base_usd'];
        $baseCoins = $calc['base_coins'];
        $profitCoins = $calc['profit_coins'];
        $bonusCoins = $calc['bonus_coins'];
        $profitUsd = $calc['profit_usd'];

        // Inferring other variables from original context
        $charger_id = auth()->user()->id;
        $charger_type = "dash";
        $user_type = 'user';
        $balance_before = $value_before;
        $transaction_type = 'admin_adjustment'; // From original code
        $userCoins = \Cache::rememberForever('user_coins', function () {
            $setting = Setting::where('key', 'user_coins')->first();
            return $setting?->value ?? 1;
        });
        $usdAmount = $userCoins > 0 ? $amount / $userCoins : 0;

        
        Charge::create([
            'charger_id' => $charger_id,
            'charger_type' => $charger_type,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'amount' => $amount,
            'amount_type' => 2,
            'usd' => $usdAmount,
            'balance_before' => $balance_before,
            'total_coins' => $totalCoins,
            'transaction_type' => $transaction_type,
            'rate_source' => 'app',
            'applied_coin_rate' => $effectiveRate,
            'base_usd' => $usd,
            'base_coins' => $baseCoins,
            'bonus_coins' => $bonusCoins,
            'profit_usd' => $profitUsd,
            'profit_coins' => $profitCoins,
        ]);
    }
}
