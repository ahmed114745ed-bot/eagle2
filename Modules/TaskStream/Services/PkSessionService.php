<?php

namespace Modules\TaskStream\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\TaskStream\Repositories\PkSessionRepository;

class PkSessionService extends TaskStreamValidationService
{
    public function __construct(
        private readonly PkSessionRepository $pkSessionRepository,
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function start(): LengthAwarePaginator
    {
        $liveRoom = $this->validateAuthLiveRoom();
        $taskId = $this->taskStreamRoomRepository->getRoomTask($liveRoom->id);
    }
}
