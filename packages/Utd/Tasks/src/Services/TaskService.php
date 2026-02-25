<?php

namespace Utd\Tasks\Services;

use App\Helpers\Common;
use App\Repositories\User\UserRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\DailyPrize\Http\Controllers\Api\DailyGiftController;
use Utd\Tasks\Repositories\DailyTaskRepository;
use Utd\Tasks\Repositories\DayRepository;
use Utd\Tasks\Repositories\TaskProgressRepository;
use Utd\Tasks\Repositories\TaskRewardRepository;
use Utd\Tasks\Repositories\UserDayProgressRepository;
use Utd\Tasks\Repositories\UserTaskRewardRepository;

class TaskService
{
    protected $dailyTaskRepo;

    protected $taskProgressRepo;

    protected $taskRewardRepo;

    protected $dayRepo;

    protected $dailyGiftController;

    protected $userTaskRewardRepo;

    protected $userRepository;

    protected $userDayProgressRepository;

    public function __construct(
        DailyTaskRepository $dailyTaskRepo,
        TaskProgressRepository $taskProgressRepo,
        TaskRewardRepository $taskRewardRepo,
        DayRepository $dayRepo,
        DailyGiftController $dailyGiftController,
        UserTaskRewardRepository $userTaskRewardRepo,
        UserRepository $userRepository,
        UserDayProgressRepository $userDayProgressRepository
    ) {
        $this->dailyTaskRepo = $dailyTaskRepo;
        $this->taskProgressRepo = $taskProgressRepo;
        $this->taskRewardRepo = $taskRewardRepo;
        $this->dayRepo = $dayRepo;
        $this->dailyGiftController = $dailyGiftController;
        $this->userTaskRewardRepo = $userTaskRewardRepo;
        $this->userRepository = $userRepository;
        $this->userDayProgressRepository = $userDayProgressRepository;
    }

    public function collectTaskPoints($taskId, $userId)
    {
        DB::beginTransaction();
        try {
            $task = $this->dailyTaskRepo->findOrFail($taskId);
            $taskProgress = $this->taskProgressRepo->getAll(['user_id' => $userId, 'task_id' => $taskId])->first();
            $userDayProgressRow = $this->userDayProgressRepository->getAll(['user_id' => $userId, 'day_id' => $task->day_id])->first();

            if (! $taskProgress) {
                DB::rollBack();

                return Common::apiResponse(false, 'Task progress not found', null, 404);
            }

            if ($taskProgress->is_completed || $taskProgress->is_collect) {
                DB::rollBack();

                return Common::apiResponse(true, 'Task progress already collected', null, 200);
            }

            if ($taskProgress->count === $task->count) {
                $taskProgress->is_completed = true;
                $taskProgress->is_collect = true;

                $this->taskProgressRepo->save($taskProgress);

                $user = $this->userRepository->findOrFail($userId);
                $user->total_points += $task->total_points;
                $user->save();

                if ($userDayProgressRow) {
                    $userDayProgressRow->points += $task->total_points;
                    $this->userDayProgressRepository->save($userDayProgressRow);
                } else {
                    $this->userDayProgressRepository->create([
                        'user_id' => $userId,
                        'day_id' => $task->day_id,
                        'points' => $task->total_points,
                        'is_completed' => false,
                        'get_rewards' => false,
                        'created_at' => now(),
                    ]);
                }
            }

            $allTasksCompleted = $this->areAllTasksCompleted($task->day_id, $userId);

            if ($allTasksCompleted) {
                $userDayProgressRow->is_completed = true;
                $this->userDayProgressRepository->save($userDayProgressRow);
                $this->unlockDayAndAssignRewards($task->day_id, $userId);
            }

            if ($allTasksCompleted) {
                $rewards = $this->taskRewardRepo->getAll(['day_id' => $task->day_id]);
                DB::commit();

                return Common::apiResponse(true, 'Points collected successfully and the day is completed successfully', [
                    'total_points' => $user->total_points,
                    'rewards' => $rewards,
                ], 200);
            }
            DB::commit();

            return Common::apiResponse(true, 'Points collected successfully', [
                'total_points' => $user->total_points,
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return Common::apiResponse(false, $e->getMessage(), null, 500);
        }
    }

    private function areAllTasksCompleted($dayId, $userId)
    {
        $tasks = $this->dailyTaskRepo->getAll(['day_id' => $dayId]);
        foreach ($tasks as $task) {
            $progress = $this->taskProgressRepo->getAll(['user_id' => $userId, 'task_id' => $task->id])->first();
            if (! $progress || ! $progress->is_completed) {
                return false;
            }
        }

        return true;
    }

    private function unlockDayAndAssignRewards($dayId, $userId)
    {
        $day = $this->dayRepo->findOrFail($dayId);
        if ($day) {
            $day->get_rewards = true;
            $this->dayRepo->save($day);
        }

        $nextDay = $this->dayRepo->getNextDay($dayId);
        if ($nextDay) {
            $nextDay->is_unlocked = true;
            $this->dayRepo->save($nextDay);
        }

        $user = $this->userRepository->findOrFail($userId);
        $rewards = $this->taskRewardRepo->getAll(['day_id' => $dayId]);

        foreach ($rewards as $reward) {
            if (! $this->userTaskRewardRepo->getAll(['user_id' => $userId, 'task_reward_id' => $reward->id])->first()) {
                $this->dailyGiftController->assignGiftToUser(
                    $reward->type,
                    $user,
                    $reward->target,
                    $reward->expire
                );

                $this->userTaskRewardRepo->create([
                    'user_id' => $userId,
                    'task_reward_id' => $reward->id,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
