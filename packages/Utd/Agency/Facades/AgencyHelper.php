<?php

namespace Utd\Agency\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed apiResponse($status, $message = '', $data = [], $statusCode = 200, $pagination = '', $dataKey = 'data')
 * @method static mixed getSettingValue($key, $default = null)
 * @method static mixed searchAgency($identifier)
 * @method static mixed send_firebase_notification($tokens, $title, $body)
 * @method static mixed checkUserAgencyFrozen($user)
 * @method static mixed getCoinsValue($key)
 * @method static mixed getPaginates($data)
 */
class AgencyHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'agency.helper';
    }
}
