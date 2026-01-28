<?php

namespace Utd\Agency\Contracts;

interface AgencyServiceInterface
{
    public function joinAgency($user, $request);
    public function find($agencyId);
    public function agencyTarget($userId, $user, $request);
    public function stars($agencyId, $request);
    public function heroes($agencyId, $request);
    public function agencyMembers($agencyId);
    public function acceptRequest($agencyId, $userId);
    public function rejectRequest($agencyId, $userId);
    public function kickFromAgency($userId);
    public function createAgency(array $data);
    public function updateAgency($agencyId, array $data);
    public function deleteAgency($agencyId);
    public function leaveAgency($user, $agencyId);
    public function historyLastThirtyDays($userUuid);
}
