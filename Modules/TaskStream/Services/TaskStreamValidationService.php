<?php

namespace Modules\TaskStream\Services;

use App\Helpers\Common;
use App\Models\Room;
use Exception;
use Illuminate\Http\Response;
use Modules\TaskStream\Repositories\TaskStreamRepository;
use Modules\TaskStream\Repositories\TaskStreamRoomRepository;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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
            throw new Exception(__('This room is not live in current time'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($liveRoom->uid === auth()->id()) {
            $taskStream = $this->taskStreamRepository->findByRoomId($liveRoom->id);

            if ($taskStream && $taskStream->rooms()->count() === 0) {
                throw new Exception(__('You cannot join your own task stream while there is nobody.'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
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
            throw new Exception(__('You dont have live room or not live'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
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
            throw new Exception(__('Your room is not part of this task stream.'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
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
            throw new Exception(__('This room is already part of another task stream.'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @throws Exception
     */
    protected function validateLimit($taskStream): void
    {
        $limit = Common::getConfig('max_task_stream') ?? 4;

        if ($taskStream->rooms()->count() >= $limit) {
            throw new Exception(__('This task stream has reached the maximum number of rooms allowed.'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
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

    /**
     * @throws Exception
     */
    public function checkRoomsIds($allRoomIds, $liveRoomId): void
    {
        $existing = Room::whereIn('id', $allRoomIds)->where('type','live')->where('is_live',1)->pluck('id')->toArray();
        $missing = array_diff($allRoomIds, $existing);
        if (! empty($missing)) throw new Exception(__('Some rooms are not live or do not exist: ') . implode(',', $missing), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        if (! in_array($liveRoomId, $allRoomIds)) throw new Exception(__('Host must be part of the teams'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    }
}
