<?php

namespace Modules\Wallet\Services;

use App\Helpers\Common;
use App\Models\User;
use Exception;

class CheckSystemConfigs
{
    /**
     * @throws Exception
     */
    public static function checkSystemConfigs(): void
    {
        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;

        if ($stop_all_charge == 1) {
            throw new Exception(__('api_responses.freeze_charge_settings'));
        }
    }

    /**
     * @throws Exception
     */
    public static function checkUserTransferAvailability(User $sender, User $receiver): void
    {
        if ($sender->transfer_salary == 1) {
            throw new Exception(__('api_responses.freeze_transfer_charger'));
        }

        if ($receiver->transfer_salary == 1) {
            throw new Exception(__('api_responses.freeze_transfer_receiver'));
        }
    }

    /**
     * @throws Exception
     */
    public static function getConfigRate()
    {
        $rate = Common::getCoinsValue('user_coins');

        if (!$rate){
            throw new Exception( __('please set usd_value_in_coins in configs'));
        }

        return $rate;
    }
}
