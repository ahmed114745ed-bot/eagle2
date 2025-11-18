<?php

namespace Modules\TaskStream\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\TaskStream\Entities\TaskStream;

class TaskStreamRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new TaskStream());
    }

    public function get(): LengthAwarePaginator
    {
        return $this->model->with(['rooms'])->paginate(request('per_page', 10));
    }

    public function createTask($liveRoomId)
    {
        $taskStream = $this->model->firstOrCreate(['room_id' => $liveRoomId]);

        $taskStream->rooms()->firstOrCreate([
            'room_id' => $taskStream->room_id,
        ]);

        return $taskStream;
    }

    public function createTaskRoom($taskStream, $liveRoomId)
    {
        $taskStream->rooms()->firstOrCreate([
            'room_id' => $liveRoomId,
        ]);

        return $taskStream;
    }

    public function getExistenceTask($taskStream, $liveRoomId)
    {
        return $taskStream->rooms()->where('room_id', $liveRoomId)->first();
    }

    public function findByRoomId($roomId)
    {
        return $this->model->where('room_id', $roomId)->first();
    }
}
