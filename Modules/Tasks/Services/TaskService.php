<?php

namespace Modules\Tasks\Services;

use Modules\Tasks\Repositories\Contracts\DailyTaskRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\TaskProgressRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\TaskRewardRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\DayRepositoryInterface;
//use Http\Controllers\DailyGiftController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\DailyPrize\Http\Controllers\Api\DailyGiftController;

class TaskService
{
    protected $dailyTaskRepo;
    protected $taskProgressRepo;
    protected $taskRewardRepo;
    protected $dayRepo;
    protected $dailyGiftController;

    public function __construct(
        DailyTaskRepositoryInterface $dailyTaskRepo,
        TaskProgressRepositoryInterface $taskProgressRepo,
        TaskRewardRepositoryInterface $taskRewardRepo,
        DayRepositoryInterface $dayRepo,
        DailyGiftController $dailyGiftController
    ) {
        $this->dailyTaskRepo = $dailyTaskRepo;
        $this->taskProgressRepo = $taskProgressRepo;
        $this->taskRewardRepo = $taskRewardRepo;
        $this->dayRepo = $dayRepo;
        $this->dailyGiftController = $dailyGiftController;
    }

    public function collectTaskPoints($taskId, $userId)
    {
        return DB::transaction(function () use ($taskId, $userId) {
            $task = $this->dailyTaskRepo->findById($taskId);
            $taskProgress = $this->taskProgressRepo->findUserTaskProgress($userId, $taskId);

            if (!$taskProgress) {
                throw new \Exception('Task progress not found');
            }

            if ($taskProgress->is_completed) {
                return ['message' => 'Task progress already collected'];
            }

            if ($taskProgress->count == $task->count) {
                $taskProgress->is_completed = true;
                $this->taskProgressRepo->save($taskProgress);

                $user = User::findOrFail($userId);
                $user->total_points += $task->total_points;
                $user->save();
            }

            $dayTasks = $this->dailyTaskRepo->findByDayId($task->day_id);
            $allTasksCompleted = $this->areAllTasksCompleted($dayTasks, $userId);

            if ($allTasksCompleted) {
                $this->unlockDayAndAssignRewards($task->day_id, $userId);
            }

            return [
                'message' => 'Points collected successfully',
                'total_points' => $user->total_points,
            ];
        });
    }

    private function areAllTasksCompleted($dayTasks, $userId)
    {
        foreach ($dayTasks as $dayTask) {
            $userTaskProgress = $this->taskProgressRepo->findUserTaskProgress($userId, $dayTask->id);
            if (!$userTaskProgress || !$userTaskProgress->is_completed) {
                return false;
            }
        }
        return true;
    }

    private function unlockDayAndAssignRewards($dayId, $userId)
    {
        $day = $this->dayRepo->findById($dayId);
        if ($day && !$day->is_unlocked) {
            $day->is_unlocked = true;
            $this->dayRepo->save($day);
        }

        $rewards = $this->taskRewardRepo->findByDayId($dayId);
        foreach ($rewards as $reward) {
            if (!$this->taskRewardRepo->userHasReward($userId, $reward->id)) {
                $this->dailyGiftController->assignGiftToUser(
                    $reward->type, 
                    $userId, 
                    $reward->target, 
                    $reward->expire
                );

                $this->taskRewardRepo->createUserReward([
                    'user_id' => $userId,
                    'task_reward_id' => $reward->id,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
