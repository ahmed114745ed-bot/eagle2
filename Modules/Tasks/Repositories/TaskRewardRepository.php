<?php

namespace Modules\Tasks\Repositories;


use Modules\Tasks\Repositories\Contracts\TaskRewardRepositoryInterface;
use Modules\Tasks\Entities\TaskReward;
use Modules\Tasks\Entities\UserTaskReward;

class TaskRewardRepository implements TaskRewardRepositoryInterface
{
    public function findByDayId($dayId)
    {
        return TaskReward::where('day_id', $dayId)->get();
    }

    public function userHasReward($userId, $taskRewardId)
    {
        return UserTaskReward::where('user_id', $userId)
                             ->where('task_reward_id', $taskRewardId)
                             ->exists();
    }

    public function createUserReward(array $data)
    {
        return UserTaskReward::create($data);
    }
}