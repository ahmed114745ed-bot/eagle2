<?php

namespace Modules\TaskStream\Services;

use App\Models\Pk;
use App\Models\User;
use App\Repositories\User\UserRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\TaskStream\Entities\PkSession;
use Modules\TaskStream\Repositories\TaskStreamInvitationRepository;
use Modules\TaskStream\Repositories\TaskStreamRepository;
use Modules\TaskStream\Repositories\TaskStreamRoomRepository;


class TaskStreamService extends TaskStreamValidationService
{
    public function __construct(
        protected readonly TaskStreamInvitationRepository $taskStreamInvitationRepository,
        protected readonly UserRepository $userRepository,
        TaskStreamRepository $taskStreamRepository,
        TaskStreamRoomRepository $taskStreamRoomRepository,
    )
    {
        parent::__construct($taskStreamRepository, $taskStreamRoomRepository);
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
    public function getIntoTask($taskStream)
    {
        $this->validateTaskLiveRoom($taskStream->room_id);

        $liveRoom = $this->validateAuthLiveRoom();

        $this->validateLimit($taskStream);

        $this->validateRoomInAnotherTask($taskStream->id, $liveRoom->id);

        $this->remoteUpdate($taskStream->room_id, $liveRoom->id, 1);

        if ($taskStream->rooms()->count() === 0) {
            $this->taskStreamRepository->createTaskRoom($taskStream, $taskStream->room_id);
        }

        $this->taskStreamRepository->createTaskRoom($taskStream, $liveRoom->id);

        $this->sendTaskToZego('newJoinedTaskStream', $taskStream->id, $liveRoom);

        return $taskStream->load('rooms');
    }

    /**
     * @throws Exception
     */
    public function join($data)
    {
        $taskStream = $this->taskStreamRepository->findOrFail($data['task_stream_id']);

        return $this->getIntoTask($taskStream);
    }

    /**
     * @throws Exception
     */
    public function leave($data)
    {
        $liveRoom = $this->validateAuthLiveRoom();

        $taskStream = $this->taskStreamRepository->findOrFail($data['task_stream_id']);

        $taskStreamRoom = $this->validateRoomInTask($taskStream, $liveRoom->id);

        if ($taskStream->rooms()->count() === 2) {
            $roomIds = $taskStream->rooms()->pluck('room_id')->toArray();
            $pk = PkSession::where(['status' => 1, 'task_stream_id' => $taskStreamRoom->task_stream_id])->latest()->first();
            if ($pk){
                app(PkSessionService::class)->closeLogic($pk->id, $taskStreamRoom->task_stream_id);
            }

            $this->taskStreamRepository->updateAllRemotes($roomIds);

            $taskStream->rooms()->delete();
        } else {
            $this->remoteUpdate($taskStream->room_id, $liveRoom->id, 0);
            $taskStreamRoom->delete();
        }

        return $taskStream->load('rooms');
    }

    /**
     * @throws Exception
     */
    public function sendInvitation(array $data): true
    {
        $authUser = auth()->user();
        $liveRoom = $this->validateAuthLiveRoom();
        $inviteeUserId = $data['invitee_user_id'];

        $taskStream = $this->taskStreamRepository->findOrFail($data['task_stream_id']);

        if ($inviteeUserId == $authUser->id) {
            throw new Exception(__('You cannot invite yourself.'));
        }

        if ($taskStream->room_id != $liveRoom->id) {
            $this->validateRoomInTask($taskStream, $liveRoom->id);
        }

        if ($taskStream->room_id == $liveRoom->id) {
            $roomsCount = $taskStream->rooms()->count();

            $ownerRoomExists = $this->taskStreamRepository->getExistenceTask($taskStream, $taskStream->room_id);

            if (! $ownerRoomExists && $roomsCount > 0) {
                throw new Exception(__('You cannot send an invitation while your task is empty. Please join your task first.'));
            }
        }

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
            $result = $this->getIntoTask($taskStream);

            $invitation->update(['status' => 'accepted']);

            return $result;
        }

        $invitation->update(['status' => 'rejected']);

        return $taskStream->load('rooms');
    }

    public function liveFriends(): LengthAwarePaginator
    {
        $tasks = $this->taskStreamRoomRepository->getArrayTasks();

        return $this->userRepository->friends($tasks);
    }
}
