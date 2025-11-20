<?php

namespace Modules\TaskStream\Services;

use App\Helpers\Common;
use App\Models\Room;
use Exception;
use Modules\TaskStream\Repositories\TaskStreamRepository;
use Modules\TaskStream\Repositories\TaskStreamRoomRepository;

class TaskStreamValidationService
{
    public function __construct(
        protected readonly TaskStreamRepository $taskStreamRepository,
        protected readonly TaskStreamRoomRepository $taskStreamRoomRepository,
    )
    {
    }

    /**
     * @throws Exception
     */
    protected function validateTaskLiveRoom($taskRoomId)
    {
        $liveRoom = Room::where(['id' => $taskRoomId, 'type' => 'live', 'is_live' => 1])->first();

        if (! $liveRoom){
            throw new Exception(__('This room is not live in current time'));
        }

        if ($liveRoom->uid === auth()->id()) {
            $taskStream = $this->taskStreamRepository->findByRoomId($liveRoom->id);

            if ($taskStream && $taskStream->rooms()->count() === 0) {
                throw new Exception(__('You cannot join your own task stream while there is nobody.'));
            }
        }

        return $liveRoom;
    }

    /**
     * @throws Exception
     */
    protected function validateAuthLiveRoom($checkUser = null)
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
    protected function validateRoomInTask($taskStream, $liveRoomId)
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
    protected function validateRoomInAnotherTask($taskStreamId, $liveRoomId): void
    {
        $alreadyInTask = $this->taskStreamRoomRepository->checkExistenceTask($taskStreamId, $liveRoomId);

        if ($alreadyInTask) {
            throw new Exception(__('This room is already part of another task stream.'));
        }
    }

    /**
     * @throws Exception
     */
    protected function validateLimit($taskStream): void
    {
        $limit = Common::getConfig('max_task_stream') ?? 4;

        if ($taskStream->rooms()->count() >= $limit) {
            throw new Exception(__('This task stream has reached the maximum number of rooms allowed.'));
        }
    }

    protected function remoteUpdate($taskStreamRoomId, $liveRoomId, $status): void
    {
        if ($taskStreamRoomId != $liveRoomId) {
            $myTask = $this->taskStreamRepository->findByRoomId($liveRoomId);

            if ($myTask) {
                $myTask->update(['is_remote' => $status]);
            }
        }
    }

    protected function sendTaskToZego(string $message, $taskStreamId, $liveRoom): void
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
