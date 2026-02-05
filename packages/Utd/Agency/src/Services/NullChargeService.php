<?php

namespace Utd\Agency\Services;

use Utd\Agency\Contracts\ChargeServiceInterface;

/**
 * Null Object Pattern for ChargeService
 * Used when the actual service is not available
 */
class NullChargeService implements ChargeServiceInterface
{
    public function chargeAgency(int $agencyId, float $amount, array $data = [])
    {
        return null;
    }

    public function getChargeHistory(int $agencyId, array $filters = [])
    {
        return [];
    }

    public function validateCharge(array $data): bool
    {
        return false;
    }
}
