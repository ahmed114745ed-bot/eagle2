<?php

namespace Modules\TaskStream\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Modules\TaskStream\Entities\TaskStreamRoom;

class TaskStreamRoomRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new TaskStreamRoom());
    }

    public function checkExistenceTask($taskStreamId, $liveRoomId)
    {
        return $this->model->where('task_stream_id', '<>', $taskStreamId)->where('room_id', $liveRoomId)->exists();
    }

    public function getArrayTasks()
    {
        return TaskStreamRoom::pluck('room_id')->toArray();
    }
}
