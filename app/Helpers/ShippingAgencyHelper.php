<?php

namespace App\Helpers;

use App\Models\ShippingAgency;

class ShippingAgencyHelper
{
    public static function isReliableTransferEnabled(): bool
    {
        return settings()->get('transfer_salary_reliable_shipping_agency') === 1;
    }

    public static function isVerifiedChargeForAgency(ShippingAgency $shippingAgency)
    {
        return self::isReliableTransferEnabled() && $shippingAgency->chargeAgency()->exists();
    }
}
