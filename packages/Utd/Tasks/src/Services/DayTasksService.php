<?php

namespace Utd\Tasks\Services;

use App\Helpers\Common;
use Exception;
use Utd\Tasks\Repositories\DailyTaskRepository;
use Utd\Tasks\Repositories\TaskProgressRepository;

class DayTasksService
{
    protected $dailyTaskRepository;
    protected $taskProgressRepository;

    public function __construct(DailyTaskRepository $dailyTaskRepository, TaskProgressRepository $taskProgressRepository)
    {
        $this->dailyTaskRepository = $dailyTaskRepository;
        $this->taskProgressRepository = $taskProgressRepository;
    }

    public function getDayTasks($userId, $dayId)
    {
        try {
            $tasks = $this->dailyTaskRepository->getAll(['day_id' => $dayId]);

            $taskProgress = $this->taskProgressRepository->getAll(['user_id' => $userId]);

            $tasksWithCompletion = $tasks->map(function ($task) use ($taskProgress) {
                $progress = $taskProgress->firstWhere('task_id', $task->id);
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'type' => $task->type,
                    'sub_type' => $task->sub_type,
                    'count' => $task->count,
                    'total_points' => $task->total_points,
                    'is_completed' => $progress ? $progress->is_completed : false,
                    'is_collect' => $progress ? $progress->is_collect : false
                ];
            });

            return Common::apiResponse(true, 'Tasks fetched successfully.', [
                'tasks' => $tasksWithCompletion
            ], 200);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 500);
        }
    }
}
