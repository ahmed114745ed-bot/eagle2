<?php

namespace Modules\Tasks\Repositories;


use Modules\Tasks\Repositories\Contracts\TaskProgressRepositoryInterface;
use Modules\Tasks\Entities\UserDayTaskProgress;

class TaskProgressRepository implements TaskProgressRepositoryInterface
{
    public function findUserTaskProgress($userId, $taskId)
    {
        return UserDayTaskProgress::where('user_id', $userId)
                                  ->where('task_id', $taskId)
                                  ->first();
    }

    public function save($taskProgress)
    {
        $taskProgress->save();
    }
}