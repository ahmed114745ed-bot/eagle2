<?php

namespace Utd\Agency\Services;

use Exception;
use Utd\Agency\Contracts\AgencyHostInviteServiceInterface;
use Utd\Agency\Repositories\AdminRepository;
use Utd\Agency\Repositories\AgencyHostInviteRepository;
use Utd\Agency\Repositories\UserRepository;

class AgencyHostInviteService implements AgencyHostInviteServiceInterface
{
    public function __construct(
        private readonly AgencyHostInviteRepository $agencyHostInviteRepository,
        private readonly UserRepository $userRepository,
        private readonly AdminRepository $adminRepository,
    ) {}

    public function inviteAgency($request)
    {
        $user = $this->get_user($request);
        if (! $user) {
            throw new Exception('لا يوجد مستخدم!');
        }

        $newHost = $this->userRepository->findById($request->user_id2);

        if ($newHost->agency_id !== 0) {
            throw new Exception('المستخدم موجود في وكاله!');
        }

        $check = $this->agencyHostInviteRepository->check($user->agency_id, $newHost->id);
        if ($check !== null && $check->created_at->addDays(7) > now() && $check->status === 0) {
            throw new Exception('لم يمر علي اخر دعوه 7 ايام!');
        }

        $data = [
            'user_invite_id' => $user->id,
            'agency_id' => $user->agency_id,
            'user_id' => $newHost->id,
            'status' => 0,
        ];
        $this->agencyHostInviteRepository->create($data);

        return true;
    }

    public function hostInvitation($request = null)
    {
        $user = $this->get_user($request ?? request());
        if (! $user) {
            throw new Exception('لا يوجد مستخدم!');
        }

        return $this->agencyHostInviteRepository->getByAgencyId($user->agency_id);
    }

    public function inviteAction($request)
    {
        $user = $this->get_user(request());
        if (! $user) {
            throw new Exception('لا يوجد مستخدم!');
        }

        $invitation = $this->agencyHostInviteRepository->findOrFail($request->invite_id);

        if ($invitation->created_at->addDays(7) < now()) {
            $this->agencyHostInviteRepository->updateStatus($invitation, 3);
            throw new Exception('لقد مر اكثر من 7 ايام علي الدعوه');
        }
        $this->agencyHostInviteRepository->updateStatus($invitation, $request->status);
        if ($request->status === 1) {
            $this->userRepository->updateAgencyId($user, $invitation->agency_id);
        }

        return true;
    }

    /**
     * Send invitation to host
     */
    public function sendInvitation(int $agencyId, int $hostId, array $data = [])
    {
        $check = $this->agencyHostInviteRepository->check($agencyId, $hostId);
        if ($check !== null && $check->created_at->addDays(7) > now() && $check->status === 0) {
            throw new Exception('لم يمر علي اخر دعوه 7 ايام!');
        }

        $inviteData = array_merge([
            'agency_id' => $agencyId,
            'user_id' => $hostId,
            'status' => 0,
        ], $data);

        return $this->agencyHostInviteRepository->create($inviteData);
    }

    /**
     * Accept invitation
     */
    public function acceptInvitation(int $invitationId)
    {
        $invitation = $this->agencyHostInviteRepository->findOrFail($invitationId);

        if ($invitation->created_at->addDays(7) < now()) {
            $this->agencyHostInviteRepository->updateStatus($invitation, 3);
            throw new Exception('لقد مر اكثر من 7 ايام علي الدعوه');
        }

        $this->agencyHostInviteRepository->updateStatus($invitation, 1);

        $user = $this->userRepository->findById($invitation->user_id);
        if ($user) {
            $this->userRepository->updateAgencyId($user, $invitation->agency_id);
        }

        return $invitation;
    }

    /**
     * Reject invitation
     */
    public function rejectInvitation(int $invitationId)
    {
        $invitation = $this->agencyHostInviteRepository->findOrFail($invitationId);
        $this->agencyHostInviteRepository->updateStatus($invitation, 2);

        return $invitation;
    }

    /**
     * Get pending invitations for host
     */
    public function getPendingInvitations(int $hostId)
    {
        return $this->agencyHostInviteRepository->getPendingByUserId($hostId);
    }

    public function get_user($request)
    {
        if ($request->user_id) {
            $admin = $this->adminRepository->findById($request->user()->id);
            if (isset($admin) && $admin->isRole('admin')) {
                $user = $this->userRepository->findById($request->user_id);
            } else {
                $user = null;
            }
        } else {
            $user = $this->userRepository->findById($request->user()->id);
        }

        return $user;
    }
}
