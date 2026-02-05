<?php

namespace App\Repositories;

use App\Contracts\ShippingAgencyRepositoryInterface;

class NullShippingAgencyRepository implements ShippingAgencyRepositoryInterface
{
    public function findAgencyByOwnerId(int $ownerId)
    {
        return null;
    }
}
