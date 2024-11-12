<?php

namespace Modules\Tasks\Services;

use App\Helpers\Common;
use App\Models\User;
use App\Repositories\User\UserRepository;
use Modules\Tasks\Entities\Day;
use Modules\Tasks\Entities\DailyTask;
use Modules\Tasks\Repositories\DailyTaskRepository;
use Modules\Tasks\Repositories\DayRepository;

class TaskProgressService
{
    protected $dayRepository;
    protected $userRepository;
    protected $dailyTaskRepository;

    public function __construct(DayRepository $dayRepository,UserRepository $userRepository,DailyTaskRepository $dailyTaskRepository)
    {
        $this->dayRepository = $dayRepository;
        $this->userRepository= $userRepository;
        $this->dailyTaskRepository=$dailyTaskRepository;
    }

    public function getUserProgress($userId)
    {
        try {
            $user = $this->userRepository->findUserById($userId);//User::findOrFail($userId);
            $totalPoints = $user->total_points;

            $days = $this->dayRepository->getAll(orderBy:['day_number' => 'asc']);//Day::orderBy('day_number')->get();

            $lastUnlockedDay = $days->where('is_unlocked', 0)->last();

            $tasks = [];
            if ($lastUnlockedDay) {
                $tasks = $this->dailyTaskRepository->getAll(['day_id', $lastUnlockedDay->id]);//DailyTask::where('day_id', $lastUnlockedDay->id)->get();
            }

            return Common::apiResponse(true, 'User progress fetched successfully.', [
                'total_points' => $totalPoints,
                'days' => $days,
                'last_day_tasks' => $tasks,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching user progress: ' . $e->getMessage());
            return Common::apiResponse(false, $e->getMessage(), null, 500);
        }
    }
}
