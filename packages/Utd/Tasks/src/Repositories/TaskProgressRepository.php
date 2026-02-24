<?php

namespace Utd\Tasks\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Utd\Tasks\Entities\UserDayTaskProgress;

class TaskProgressRepository extends AbstractRepository
{
    public function __construct(UserDayTaskProgress $model)
    {
        parent::__construct($model);
    }

    public function save($taskProgress)
    {
        $taskProgress->save();
    }

    public function getByUserIdAndTaskId($userId, $taskId)
    {
        return $this->model->where('user_id', $userId)->where('task_id', $taskId)->first();
    }
}
