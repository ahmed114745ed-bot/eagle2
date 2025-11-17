<?php

namespace Modules\MixStream\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Modules\MixStream\Entities\MixStreamInvitation;

class MixStreamInvitationRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new MixStreamInvitation());
    }

    public function createInvitation($mixStreamId, $inviterUserId, $inviteeUserId)
    {
        return $this->model->create([
            'mix_stream_id' => $mixStreamId,
            'inviter_user_id' => $inviterUserId,
            'invitee_user_id' => $inviteeUserId,
            'status' => 'pending',
        ]);
    }


    public function findPendingInvitation($mixStreamId, $inviteeUserId)
    {
        return $this->model
            ->where('mix_stream_id', $mixStreamId)
            ->where('invitee_user_id', $inviteeUserId)
            ->where('status', 'pending')
            ->first();
    }
}
