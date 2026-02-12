<?php

namespace Utd\Agency\Contracts;

interface AgencyHostInviteServiceInterface
{
    /**
     * Send invitation to host
     *
     * @return mixed
     */
    public function sendInvitation(int $agencyId, int $hostId, array $data = []);

    /**
     * Accept invitation
     *
     * @return mixed
     */
    public function acceptInvitation(int $invitationId);

    /**
     * Reject invitation
     *
     * @return mixed
     */
    public function rejectInvitation(int $invitationId);

    /**
     * Get pending invitations for host
     *
     * @return mixed
     */
    public function getPendingInvitations(int $hostId);

    /**
     * Invite user to agency
     *
     * @param  mixed  $request
     * @return mixed
     */
    public function inviteAgency($request);

    /**
     * Get host invitations
     *
     * @param  mixed  $request
     * @return mixed
     */
    public function hostInvitation($request);

    /**
     * Handle invite action
     *
     * @param  mixed  $request
     * @return mixed
     */
    public function inviteAction($request);
}
