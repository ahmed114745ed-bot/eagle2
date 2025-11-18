<?php

namespace Modules\TaskStream\Services;

use App\Helpers\Common;
use App\Models\User;
use App\Repositories\User\UserRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\TaskStream\Repositories\TaskStreamInvitationRepository;
use Modules\TaskStream\Repositories\TaskStreamRepository;
use Modules\TaskStream\Repositories\TaskStreamRoomRepository;

class TaskStreamService
{
    public function __construct(
        private readonly TaskStreamRepository           $taskStreamRepository,
        private readonly TaskStreamRoomRepository       $taskStreamRoomRepository,
        private readonly TaskStreamInvitationRepository $taskStreamInvitationRepository,
        private readonly UserRepository                 $userRepository,
    )
    {
    }

    public function index(): LengthAwarePaginator
    {
        return $this->taskStreamRepository->get();
    }

    /**
     * @throws Exception
     */
    public function store()
    {
        $liveRoom = $this->validateAuthLiveRoom();

        $taskStream = $this->taskStreamRepository->createTask($liveRoom->id);

        $this->sendTaskToZego('newTaskStream', $taskStream->id, $liveRoom);

        return $taskStream->load('rooms');
    }

    /**
     * @throws Exception
     */
    public function join($data)
    {
        $liveRoom = $this->validateAuthLiveRoom();

        $taskStream = $this->taskStreamRepository->findOrFail($data['task_stream_id']);

        $this->validateLimit($taskStream);

        $this->validateRoomInAnotherTask($taskStream->id, $liveRoom->id);

        $this->taskStreamRepository->createTaskRoom($taskStream, $liveRoom->id);

        $this->sendTaskToZego('newJoinedTaskStream', $taskStream->id, $liveRoom);

        return $taskStream->load('rooms');
    }

    /**
     * @throws Exception
     */
    public function leave($data)
    {
        $liveRoom = $this->validateAuthLiveRoom();

        $taskStream = $this->taskStreamRepository->findOrFail($data['task_stream_id']);

        $taskStreamRoom = $this->validateRoomNotInTask($taskStream, $liveRoom->id);

        $taskStreamRoom->delete();

//        if ($taskStream->rooms()->count() === 0) {
//            $taskStream->delete();
//        }

        //TODO 3.If host is in an active PK → trigger PK leave logic (see §3.3).
        return $taskStream->load('rooms');
    }

    /**
     * Host invites another user to join their task stream.
     *
     * @throws Exception
     */
    public function sendInvitation(array $data): true
    {
        $authUser = auth()->user();
        $liveRoom = $this->validateAuthLiveRoom();

        $taskStream = $this->taskStreamRepository->findOrFail($data['task_stream_id']);
        $inviteeUserId = $data['invitee_user_id'];

        if ($inviteeUserId == $authUser->id) {
            throw new Exception(__('You cannot invite yourself.'));
        }

        $this->validateRoomNotInTask($taskStream, $liveRoom->id);

        $invitee = User::findOrFail($inviteeUserId);

        $inviteeLiveRoom = $this->validateAuthLiveRoom($invitee);

        if ($inviteeLiveRoom) {
            $isInviteeInTask = $this->taskStreamRepository->getExistenceTask($taskStream, $inviteeLiveRoom->id);

            if ($isInviteeInTask) {
                throw new Exception(__('This user is already part of your task stream.'));
            }

            $this->validateRoomInAnotherTask($taskStream->id, $inviteeLiveRoom->id);
        }

        $existing = $this->taskStreamInvitationRepository->findPendingInvitation($taskStream->id, $inviteeUserId);

        if ($existing) {
            throw new Exception(__('This user already has a pending invitation.'));
        }

        $this->taskStreamInvitationRepository->createInvitation($taskStream->id, $authUser->id, $inviteeUserId);

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
        $taskStream = $this->taskStreamRepository->findOrFail($data['task_stream_id']);

        $invitation = $this->taskStreamInvitationRepository->findPendingInvitation($taskStream->id, $authUser->id);

        if (! $invitation) {
            throw new Exception(__('No pending invitation found for this task.'));
        }

        if ($status === 'accept') {
            $liveRoom = $this->validateAuthLiveRoom();

            $this->validateLimit($taskStream);

            $this->validateRoomInAnotherTask($taskStream->id, $liveRoom->id);

            $this->taskStreamRepository->createTaskRoom($taskStream, $liveRoom->id);

            $invitation->update(['status' => 'accepted']);

            $this->sendTaskToZego('newJoinedTaskStream', $taskStream->id, $liveRoom);

            return $taskStream->load('rooms');
        }

        $invitation->update(['status' => 'rejected']);

        return $taskStream->load('rooms');
    }

    public function liveFriends(): LengthAwarePaginator
    {
        $tasks = $this->taskStreamRoomRepository->getArrayTasks();

        return $this->userRepository->friends($tasks);
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
    public function validateRoomNotInTask($taskStream, $liveRoomId)
    {
        $taskStreamRoom = $this->taskStreamRepository->getExistenceTask($taskStream, $liveRoomId);

        if (!$taskStreamRoom) {
            throw new Exception(__('Your room is not part of this task stream.'));
        }

        return $taskStreamRoom;
    }

    /**
     * @throws Exception
     */
    public function validateRoomInAnotherTask($taskStreamId, $liveRoomId): void
    {
        $alreadyInTask = $this->taskStreamRoomRepository->checkExistenceTask($taskStreamId, $liveRoomId);

        if ($alreadyInTask) {
            throw new Exception(__('This room is already part of another task stream.'));
        }
    }

    /**
     * @throws Exception
     */
    public function validateLimit($taskStream): void
    {
        $limit = Common::getConfig('max_task_stream') ?? 4;

        if ($taskStream->rooms()->count() >= $limit) {
            throw new Exception(__('This task stream has reached the maximum number of rooms allowed.'));
        }
    }

    public function sendTaskToZego(string $message, $taskStreamId, $liveRoom): void
    {
        $data = [
            "messageContent" => [
                "message" => $message,
                'task_stream' => $taskStreamId,
                'room_id' => $liveRoom->id
            ]
        ];
        $json = json_encode($data);

        Common::sendToZego('SendCustomCommand', $liveRoom->id, $liveRoom->uid, $json);
    }
}
