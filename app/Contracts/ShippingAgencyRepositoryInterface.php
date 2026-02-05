<?php

namespace App\Contracts;

interface ShippingAgencyRepositoryInterface
{
    public function findAgencyByOwnerId(int $ownerId);
}
