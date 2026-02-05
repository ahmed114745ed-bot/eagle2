<?php

namespace Utd\Agency\Services;

use Utd\Agency\Contracts\AgencyServiceInterface;

/**
 * Null Object Pattern for AgencyService
 * Used when the actual service is not available
 */
class NullAgencyService implements AgencyServiceInterface
{
    public function joinAgency($user, $request)
    {
        return null;
    }

    public function createAgency(array $data)
    {
        return null;
    }

    public function getAgency(int $id)
    {
        return null;
    }

    public function updateAgency(int $id, array $data)
    {
        return null;
    }

    public function deleteAgency(int $id): bool
    {
        return false;
    }

    public function getAgencyMembers(int $agencyId)
    {
        return [];
    }

    public function acceptJoinRequest(int $requestId)
    {
        return null;
    }

    public function rejectJoinRequest(int $requestId)
    {
        return null;
    }
}
