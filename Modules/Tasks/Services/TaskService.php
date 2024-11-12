<?php

namespace Modules\Tasks\Services;

use Modules\Tasks\Repositories\Contracts\DailyTaskRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\TaskProgressRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\TaskRewardRepositoryInterface;
use Modules\Tasks\Repositories\Contracts\DayRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\DailyPrize\Http\Controllers\Api\DailyGiftController;
use App\Helpers\Common;
use Modules\Tasks\Entities\Day;
use Modules\Tasks\Entities\TaskReward;
use Modules\Tasks\Repositories\DailyTaskRepository;
use Modules\Tasks\Repositories\DayRepository;
use Modules\Tasks\Repositories\TaskProgressRepository;
use Modules\Tasks\Repositories\TaskRewardRepository;
use Modules\Tasks\Repositories\UserTaskRewardRepository;

class TaskService
{
    protected $dailyTaskRepo;
    protected $taskProgressRepo;
    protected $taskRewardRepo;
    protected $dayRepo;
    protected $dailyGiftController;
    protected $userTaskRewardRepo;

    public function __construct(
        DailyTaskRepository $dailyTaskRepo,
        TaskProgressRepository $taskProgressRepo,
        TaskRewardRepository $taskRewardRepo,
        DayRepository $dayRepo,
        DailyGiftController $dailyGiftController,
        UserTaskRewardRepository $userTaskRewardRepo
    ) {
        $this->dailyTaskRepo = $dailyTaskRepo;
        $this->taskProgressRepo = $taskProgressRepo;
        $this->taskRewardRepo = $taskRewardRepo;
        $this->dayRepo = $dayRepo;
        $this->dailyGiftController = $dailyGiftController;
        $this->userTaskRewardRepo= $userTaskRewardRepo;
    }

    public function collectTaskPoints($taskId, $userId)
    {
        try {
            $response = DB::transaction(function () use ($taskId, $userId) {
                $task = $this->dailyTaskRepo->findOrFail($taskId);
                $taskProgress = $this->taskProgressRepo->getAll(['user_id' => $userId,'task_id' => $taskId])->first(); //findUserTaskProgress($userId, $taskId);

                if (!$taskProgress) {
                    return Common::apiResponse(false, 'Task progress not found', null, 404);
                }

                if ($taskProgress->is_completed) {
                    return Common::apiResponse(true, 'Task progress already collected', null, 200);
                }

                if ($taskProgress->count == $task->count) {
                    $taskProgress->is_completed = true;
                    $this->taskProgressRepo->save($taskProgress);

                    $user = User::findOrFail($userId);
                    $user->total_points += $task->total_points;
                    $user->save();
                }

                $dayTasks = $this->dailyTaskRepo->getAll(['day_id' => $task->day_id]);//findByDayId($task->day_id);
                $allTasksCompleted = $this->areAllTasksCompleted($dayTasks, $userId);

                $day = $this->dayRepo->findOrFail($task->day_id);
                if($day->is_unlocked)
                {
                    return Common::apiResponse(true,'day already unlocked and rewards assigned to user',null,200);
                }

                if ($allTasksCompleted) {
                    $this->unlockDayAndAssignRewards($task->day_id, $userId);
                }
                if($allTasksCompleted)
                {
                    $rewards = $this->taskRewardRepo->getAll(['day_id' => $task->day_id]);//findByDayId($task->day_id);//TaskReward::where('day_id', $task->day_id)->get();
                    return Common::apiResponse(true, 'Points collected successfully and the day is completed successfully', [
                        'total_points' => $user->total_points,
                        'rewards' => $rewards
                    ], 200);
                }
                else 
                {
                    return Common::apiResponse(true, 'Points collected successfully', [
                        'total_points' => $user->total_points,
                    ], 200);
                }
            });

            return $response;
        } catch (\Exception $e) {
            \Log::error('Error collecting task points: ' . $e->getMessage());
            return Common::apiResponse(false, $e->getMessage(), null, 500);
        }
    }

    private function areAllTasksCompleted($dayTasks, $userId)
    {
        foreach ($dayTasks as $dayTask) {
            $userTaskProgress = $this->taskProgressRepo->getAll(['user_id' => $userId,'task_id' => $dayTask->id])->first();//->findUserTaskProgress($userId, $dayTask->id);
            if (!$userTaskProgress || !$userTaskProgress->is_completed) {
                return false;
            }
        }
        return true;
    }

    private function unlockDayAndAssignRewards($dayId, $userId)
    {
        $day = $this->dayRepo->findOrFail($dayId);
        if ($day && !$day->is_unlocked) {
            $day->is_unlocked = true;
            $this->dayRepo->save($day);
        }

        $rewards = $this->taskRewardRepo->getAll(['day_id' => $dayId]);//->findByDayId($dayId);
        foreach ($rewards as $reward) {
            if (!$this->userTaskRewardRepo->getAll(['user_id' => $userId,'task_reward_id' => $reward->id])){//userHasReward($userId, $reward->id)) {
                $this->dailyGiftController->assignGiftToUser(
                    $reward->type, 
                    $userId, 
                    $reward->target, 
                    $reward->expire
                );

                $this->userTaskRewardRepo->create([//->createUserReward([
                    'user_id' => $userId,
                    'task_reward_id' => $reward->id,
                    'created_at' => now(),
                ]);
            }
        }
    }
}