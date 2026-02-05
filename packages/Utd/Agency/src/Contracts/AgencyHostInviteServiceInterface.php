<?php

namespace Utd\Agency\Contracts;

interface AgencyHostInviteServiceInterface
{
    /**
     * Send invitation to host
     *
     * @param int $agencyId
     * @param int $hostId
     * @param array $data
     * @return mixed
     */
    public function sendInvitation(int $agencyId, int $hostId, array $data = []);

    /**
     * Accept invitation
     *
     * @param int $invitationId
     * @return mixed
     */
    public function acceptInvitation(int $invitationId);

    /**
     * Reject invitation
     *
     * @param int $invitationId
     * @return mixed
     */
    public function rejectInvitation(int $invitationId);

    /**
     * Get pending invitations for host
     *
     * @param int $hostId
     * @return mixed
     */
    public function getPendingInvitations(int $hostId);
}
