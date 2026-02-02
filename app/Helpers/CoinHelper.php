<?php

namespace App\Helpers;

use App\Models\User;
use Utd\Agency\Entities\ShippingAgency;
use App\Models\CoinLog;
use Modules\Public\Http\Services\UserCounterServices;

class CoinHelper
{
    /**
     * يطبق عملية شحن الكوينز حسب النوع
     *
     * @param CoinLog $coinLog
     * @param bool $markAsProcessed  إذا true سيتم تحديث الحالة إلى 1
     * @return bool
     */
    public static function applyCoinLog(CoinLog $coinLog, bool $markAsProcessed = true): bool
    {
        $modelClass = match ($coinLog->user_type) {
            'user' => User::class,
            'shipping_agency' => ShippingAgency::class,
            default => null,
        };

        if (!$modelClass) {
            return false;
        }

        $owner = $modelClass::find($coinLog->model_id);

        if (!$owner) {
            return false;
        }

        $owner->increment('di', $coinLog->obtained_coins);

        if ($markAsProcessed) {
            $coinLog->update(['status' => 1]);
        }

        if($coinLog->user_type == 'user'){
            Common::sendOfficialMessage (@$owner->id,__('congratulations'),__('your recharge success'));
            (new UserCounterServices)->eventUser($owner,'official-messages');
    
        }
      
        return  $owner;
    }
}
