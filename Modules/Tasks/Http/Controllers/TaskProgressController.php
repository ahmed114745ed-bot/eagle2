<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Day;
use App\Models\DailyTask;
use Illuminate\Http\Request;
use Modules\Tasks\Entities\DailyTask as EntitiesDailyTask;
use Modules\Tasks\Entities\Day as EntitiesDay;

class TaskProgressController extends Controller
{
    public function getUserProgress($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $totalPoints = $user->total_points;
    
            /*$days = EntitiesDay::with('userDaysProgress')
                ->where('is_unlocked', true)
                ->orderBy('day_number')
                ->get();*/
            $days = EntitiesDay::orderBy('day_number')->get();
            
            $lastUnlockedDay = $days->where('is_unlocked', 0)->last();
            
            $tasks = [];
            if ($lastUnlockedDay) {
                $tasks = EntitiesDailyTask::where('day_id', $lastUnlockedDay->id)->get();
            }
    
            $response = [
                'total_points' => $totalPoints,
                'days' => $days,
                'last_day_tasks' => $tasks,
            ];
    
            return response()->json($response, 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching user progress: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
        //\Log::info('Getting user progress for userId: ' . $userId);
        //return response()->json(['message' => 'Route is working'], 200);
    }
}




