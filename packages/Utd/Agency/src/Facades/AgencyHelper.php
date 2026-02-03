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
 * @method static bool hasInPack($userId, $packId, $checkActive = false)
 * @method static mixed wareUserVip($userId, $packId, $attribute)
 * @method static int level_center_min($userId)
 * @method static int ovip_center($userId)
 * @method static array getChargerInfo($charge)
 * @method static array getReceiverInfo($charge)
 */
class AgencyHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'agency.helper';
    }
}
