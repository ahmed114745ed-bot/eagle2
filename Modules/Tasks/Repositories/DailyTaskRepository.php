<?php

namespace Modules\Tasks\Repositories;

use App\Models\EntitiesDailyTask;
use Modules\Tasks\Repositories\Contracts\DailyTaskRepositoryInterface;
use Modules\Tasks\Entities\DailyTask;

class DailyTaskRepository implements DailyTaskRepositoryInterface
{
    public function findById($taskId)
    {
        return DailyTask::findOrFail($taskId);
    }

    public function findByDayId($dayId)
    {
        return DailyTask::where('day_id', $dayId)->get();
    }
}
