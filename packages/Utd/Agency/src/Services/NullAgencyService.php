<?php

namespace Utd\Agency\Services;

use Utd\Agency\Contracts\AgencyServiceInterface;

/**
 * Null Object Pattern for AgencyService
 * Used when the actual service is not available
 */
class NullAgencyService implements AgencyServiceInterface
{
    public function find($id)
    {
        return null;
    }

    public function joinAgency($user, $request)
    {
        return null;
    }

    public function agencyMembers($agencyId)
    {
        return [];
    }

    public function agencyTarget($agencyId, $user, $request)
    {
        return null;
    }

    public function stars($agencyId, $request)
    {
        return [];
    }

    public function heroes($agencyId, $request)
    {
        return [];
    }

    public function showRequests($userId)
    {
        return [];
    }

    public function requestAction($owner, $request)
    {
        return null;
    }

    public function listOption($agencyId)
    {
        return [];
    }

    public function historySearch($agencyId, $request)
    {
        return [];
    }

    public function update($userId, $agencyId, $request)
    {
        return null;
    }

    public function userHandlingRequest($userId, $agencyId, $type)
    {
        return null;
    }

    public function allAgencyCharged($agencyId)
    {
        return [];
    }

    public function gitOldAgencies($userId)
    {
        return [];
    }

    public function create($userId, $request)
    {
        return null;
    }

    public function actionRequestAgency($request)
    {
        return null;
    }

    public function allRequest()
    {
        return [];
    }

    public function historyLastThirtyDays($userUuid)
    {
        return [];
    }

    public function agencyReport($agencyId)
    {
        return null;
    }

    public function leaveAgency($userId, $agency)
    {
        return null;
    }

    public function handlingRequest($agencyId, $userId)
    {
        return null;
    }

    public function kickAgency($user, $userId)
    {
        return null;
    }

    public function filter($keyword)
    {
        return [];
    }

    public function dailyReport($user, $month, $year, $agencyId)
    {
        return null;
    }

    public function dataAgency()
    {
        return null;
    }

    public function hostReport($id)
    {
        return null;
    }

    public function hostDailyReport($request)
    {
        return null;
    }

    public function editAgency($request)
    {
        return null;
    }
}
