<?php

namespace Modules\MixStream\Services;

use App\Helpers\Common;
use App\Models\User;
use App\Tik\Repositories\UserRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\MixStream\Repositories\MixStreamInvitationRepository;
use Modules\MixStream\Repositories\MixStreamRepository;
use Modules\MixStream\Repositories\MixStreamRoomRepository;

class MixStreamService
{
    public function __construct(
        private readonly MixStreamRepository $mixStreamRepository,
        private readonly MixStreamRoomRepository $mixStreamRoomRepository,
        private readonly MixStreamInvitationRepository $mixStreamInvitationRepository,
    )
    {
    }

    public function index(): LengthAwarePaginator
    {
        return $this->mixStreamRepository->get();
    }

    /**
     * @throws Exception
     */
    public function store()
    {
        $liveRoom = $this->validateAuthLiveRoom();

        $mixStream = $this->mixStreamRepository->createMix($liveRoom->id);

        $this->sendMixToZego('newMixStream', $mixStream->id, $liveRoom);

        return $mixStream->load('rooms');
    }

    /**
     * @throws Exception
     */
    public function join($data)
    {
        $liveRoom = $this->validateAuthLiveRoom();

        $mixStream = $this->mixStreamRepository->findOrFail($data['mix_stream_id']);

        $this->validateRoomInMix($mixStream->id, $liveRoom->id);

        $this->mixStreamRepository->createMixRoom($mixStream, $liveRoom->id);

        $this->sendMixToZego('newJoinedMixStream', $mixStream->id, $liveRoom);

        return $mixStream->load('rooms');
    }

    /**
     * @throws Exception
     */
    public function leave($data)
    {
        $liveRoom = $this->validateAuthLiveRoom();

        $mixStream = $this->mixStreamRepository->findOrFail($data['mix_stream_id']);

        $mixStreamRoom = $this->validateRoomNotInMix($mixStream, $liveRoom->id);

        $mixStreamRoom->delete();

        if ($mixStream->rooms()->count() === 0) {
            $mixStream->delete();
        }

        //TODO 3.If host is in an active PK → trigger PK leave logic (see §3.3).
        return $mixStream->load('rooms');
    }

    /**
     * Host invites another user to join their mix stream.
     *
     * @throws Exception
     */
    public function sendInvitation(array $data): true
    {
        $authUser = auth()->user();
        $liveRoom = $this->validateAuthLiveRoom();

        $mixStream = $this->mixStreamRepository->findOrFail($data['mix_stream_id']);
        $inviteeUserId = $data['invitee_user_id'];

        if ($inviteeUserId == $authUser->id) {
            throw new Exception(__('You cannot invite yourself.'));
        }

        $this->validateRoomNotInMix($mixStream, $liveRoom->id);

        $invitee = User::findOrFail($inviteeUserId);

        $inviteeLiveRoom = $this->validateAuthLiveRoom($invitee);

        if ($inviteeLiveRoom) {
            $isInviteeInMix = $this->mixStreamRepository->getExistenceMix($mixStream, $inviteeLiveRoom->id);

            if ($isInviteeInMix) {
                throw new Exception(__('This user is already part of your mix stream.'));
            }

            $this->validateRoomInMix($mixStream->id, $inviteeLiveRoom->id);
        }

        $existing = $this->mixStreamInvitationRepository->findPendingInvitation($mixStream->id, $inviteeUserId);

        if ($existing) {
            throw new Exception(__('This user already has a pending invitation.'));
        }

        $this->mixStreamInvitationRepository->createInvitation($mixStream->id, $authUser->id, $inviteeUserId);

        return true;
    }

    /**
     * Invited user responds to an invitation (accept or reject)
     *
     * @throws Exception
     */
    public function respondInvitation(array $data)
    {
        $authUser = auth()->user();
        $status = $data['status'];
        $mixStream = $this->mixStreamRepository->findOrFail($data['mix_stream_id']);

        $invitation = $this->mixStreamInvitationRepository->findPendingInvitation($mixStream->id, $authUser->id);

        if (! $invitation) {
            throw new Exception(__('No pending invitation found for this mix.'));
        }

        if ($status === 'accept') {
            $liveRoom = $this->validateAuthLiveRoom();

            $this->validateRoomInMix($mixStream->id, $liveRoom->id);

            $this->mixStreamRepository->createMixRoom($mixStream, $liveRoom->id);

            $invitation->update(['status' => 'accepted']);

            $this->sendMixToZego('newJoinedMixStream', $mixStream->id, $liveRoom);

            return $mixStream->load('rooms');
        }

        $invitation->update(['status' => 'rejected']);

        return $mixStream->load('rooms');
    }

    /**
     * @throws Exception
     */
    public function validateAuthLiveRoom($checkUser = null)
    {
        $user = $checkUser ?: auth()->user();
        $liveRoom = $user->ownerRoom()->where('type', 'live')->where('is_live', 1)->first();

        if (! $liveRoom){
            throw new Exception(__('You dont have live room or not live'));
        }

        return $liveRoom;
    }

    /**
     * @throws Exception
     */
    public function validateRoomNotInMix($mixStream, $liveRoomId)
    {
        $mixStreamRoom = $this->mixStreamRepository->getExistenceMix($mixStream, $liveRoomId);

        if (!$mixStreamRoom) {
            throw new Exception(__('Your room is not part of this mix stream.'));
        }

        return $mixStreamRoom;
    }

    /**
     * @throws Exception
     */
    public function validateRoomInMix($mixStreamId, $liveRoomId): void
    {
        $alreadyInMix = $this->mixStreamRoomRepository->checkExistenceMix($mixStreamId, $liveRoomId);

        if ($alreadyInMix) {
            throw new Exception(__('This room is already part of another mix stream.'));
        }
    }

    public function sendMixToZego(string $message, $mixStreamId, $liveRoom): void
    {
        $data = [
            "messageContent" => [
                "message" => $message,
                'mix_stream' => $mixStreamId,
                'room_id' => $liveRoom->id
            ]
        ];
        $json = json_encode($data);

        Common::sendToZego('SendCustomCommand', $liveRoom->id, $liveRoom->uid, $json);
    }
}
