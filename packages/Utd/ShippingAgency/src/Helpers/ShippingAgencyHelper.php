<?php

namespace Utd\ShippingAgency\Helpers;

use Utd\ShippingAgency\Entities\ShippingAgency;

class ShippingAgencyHelper
{
    public static function isReliableTransferEnabled(): bool
    {
        return settings()->get('transfer_salary_reliable_shipping_agency') == 1;
    }

    public static function isVerifiedChargeForAgency(ShippingAgency $agency): bool
    {
        if (self::isReliableTransferEnabled()) {
            return $agency->chargeAgency()->exists();
        }

        return true;
    }
}
