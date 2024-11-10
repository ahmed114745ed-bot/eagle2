<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Day;
use App\Models\DailyTask;
use Illuminate\Http\Request;
use Modules\Tasks\Entities\DailyTask as EntitiesDailyTask;
use Modules\Tasks\Entities\Day as EntitiesDay;
use Modules\Tasks\Entities\TaskReward;
use Modules\Tasks\Entities\UserDayTaskProgress;
use Modules\Tasks\Entities\UserTaskReward;
use Modules\DailyPrize\Http\Controllers\Api\DailyGiftController;
use Illuminate\Support\Facades\DB;

class TaskCompleteController extends Controller
{
    public function collectTaskPoints($taskId, Request $request, DailyGiftController $dailyGiftController)
    {
        try {
            return DB::transaction(function () use ($taskId, $request, $dailyGiftController) {
                $userId = $request->user()->id;

                $task = EntitiesDailyTask::findOrFail($taskId);
                $taskProgress = UserDayTaskProgress::where('user_id', $userId)
                    ->where('task_id', $taskId)
                    ->first();

                if (!$taskProgress) {
                    return response()->json(['error' => 'Task progress not found'], 404);
                }

                if ($taskProgress->is_completed) {
                    return response()->json(['error' => 'Task progress already collected'], 200);
                }

                if ($taskProgress->count == $task->count) {
                    $taskProgress->is_completed = true;
                    $taskProgress->save();

                    $user = User::findOrFail($userId);
                    $user->total_points += $task->total_points;
                    $user->save();
                }

                $dayTasks = EntitiesDailyTask::where('day_id', $task->day_id)->get();
                $allTasksCompleted = true;

                foreach ($dayTasks as $dayTask) {
                    $userTaskProgress = UserDayTaskProgress::where('user_id', $userId)
                        ->where('task_id', $dayTask->id)
                        ->first();

                    if (!$userTaskProgress || !$userTaskProgress->is_completed) {
                        $allTasksCompleted = false;
                        break;
                    }
                }

                $rewards = [];

                if ($allTasksCompleted) {
                    $day = EntitiesDay::find($task->day_id);
                    if ($day && !$day->is_unlocked) {
                        $day->is_unlocked = true;
                        $day->save();
                    }

                    $rewards = TaskReward::where('day_id', $task->day_id)->get();

                    foreach ($rewards as $reward) {
                        $existingUserReward = UserTaskReward::where('user_id', $userId)
                            ->where('task_reward_id', $reward->id)
                            ->first();

                        if (!$existingUserReward) {
                            $dailyGiftController->assignGiftToUser(
                                $reward->type, 
                                $user, 
                                $reward->target, 
                                $reward->expire
                            );
                            $userReward = UserTaskReward::create([
                                'user_id' => $userId,
                                'task_reward_id' => $reward->id,
                                'created_at' => now(),
                            ]);
                            //$rewards[] = $userReward;
                        }
                    }

                    if ($day && $day->is_unlocked && count($rewards) > 0) {
                        return response()->json([
                            'message' => 'Points collected successfully, rewards available.',
                            'rewards' => $rewards,
                            'total_points' => $user->total_points,
                        ], 200);
                    }
                }

                return response()->json([
                    'message' => 'Points collected successfully',
                    'total_points' => $user->total_points,
                ], 200);
            });
        } catch (\Exception $e) {
            \Log::error('Error collecting task points: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

}




